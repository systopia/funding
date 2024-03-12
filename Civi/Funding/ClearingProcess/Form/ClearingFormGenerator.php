<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Form;

use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Clearing\CostItem\ClearingCostItemsJsonFormsGenerator;
use Civi\Funding\ApplicationProcess\Clearing\ResourcesItem\ClearingResourcesItemsJsonFormsGenerator;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\Traits\HasClearingReviewPermissionTrait;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Form\JsonSchema\JsonSchemaComment;
use Civi\Funding\Util\ArrayUtil;
use Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNull;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type clearingItemRecordT array{
 *   _id: int|null,
 *   amount: float,
 *   file: string|null,
 *   description: string,
 *   amountAdmitted: ?float,
 * }
 *
 * @phpstan-type clearingFormDataT array{
 *   _action: string,
 *   costItems?: array<int, array{records: list<clearingItemRecordT>}>,
 *   resourcesItems?: array<int, array{records: list<clearingItemRecordT>}>,
 *   reportData?: array<string, mixed>,
 *   comment?: array{text: string, type: 'internal'|'external'},
 * }
 *
 * This class generates a JSON Forms specification that has a JSON schema that
 * validates the data specified in clearingFormDataT. (For displaying purposes
 * costItems and resourcesItems have additional properties.)
 */
final class ClearingFormGenerator {

  use HasClearingReviewPermissionTrait;

  private ClearingActionsDeterminer $actionsDeterminer;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormCreateHandlerInterface $applicationFormCreateHandler;

  private ClearingCostItemsJsonFormsGenerator $clearingCostItemsJsonFormsGenerator;

  private ClearingResourcesItemsJsonFormsGenerator $clearingResourcesItemsJsonFormsGenerator;

  private ReportFormFactoryInterface $reportFormFactory;

  public function __construct(
    ClearingActionsDeterminer $actionsDeterminer,
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormCreateHandlerInterface $applicationFormCreateHandler,
    ClearingCostItemsJsonFormsGenerator $clearingCostItemsJsonFormsGenerator,
    ClearingResourcesItemsJsonFormsGenerator $clearingResourcesItemsJsonFormsGenerator,
    ReportFormFactoryInterface $reportDataFormFactory
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->applicationFormCreateHandler = $applicationFormCreateHandler;
    $this->clearingCostItemsJsonFormsGenerator = $clearingCostItemsJsonFormsGenerator;
    $this->clearingResourcesItemsJsonFormsGenerator = $clearingResourcesItemsJsonFormsGenerator;
    $this->reportFormFactory = $reportDataFormFactory;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function generateForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    $applicationProcessBundle = $clearingProcessBundle->getApplicationProcessBundle();
    $applicationForm = $this->applicationFormCreateHandler->handle(new ApplicationFormCreateCommand(
      $applicationProcessBundle,
      $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle)
    ));
    $applicationForm = new JsonFormsForm($applicationForm->getJsonSchema(), $applicationForm->getUiSchema());

    $costItemsForm = $this->clearingCostItemsJsonFormsGenerator->generate($applicationProcessBundle, $applicationForm);
    $resourcesItemsForm = $this->clearingResourcesItemsJsonFormsGenerator->generate(
      $applicationProcessBundle,
      $applicationForm
    );

    $keywords = ArrayUtil::mergeRecursive(
      $costItemsForm->getJsonSchema()->toArray(),
      $resourcesItemsForm->getJsonSchema()->toArray(),
    );

    $categories = [];
    if ([] !== $keywords) {
      $categories[] = new JsonFormsCategory(E::ts('Proofs'), [
        $costItemsForm->getUiSchema(),
        $resourcesItemsForm->getUiSchema(),
      ]);
    }

    $reportDataForm = $this->reportFormFactory->createReportForm($clearingProcessBundle);
    if ([] !== $reportDataForm->getJsonSchema()->getKeywords()) {
      $keywords = ArrayUtil::mergeRecursive($keywords, $reportDataForm->getJsonSchema()->toArray());
      $reportCategoryLabel = $reportDataForm->getUiSchema()['label'] ?? E::ts('Report');
      Assert::string($reportCategoryLabel);
      $categories[] = new JsonFormsCategory($reportCategoryLabel, [$reportDataForm->getUiSchema()]);
    }

    if ([] !== $categories) {
      $elements = [new JsonFormsCategorization($categories)];

      $actions = $this->actionsDeterminer->getActions(
        $clearingProcessBundle->getClearingProcess()->getFullStatus(),
        $clearingProcessBundle->getFundingCase()->getPermissions()
      );

      foreach ($actions as $name => $label) {
        $elements[] = new JsonFormsSubmitButton('#/properties/_action', $name, $label);
      }
    }
    else {
      $elements = [new JsonFormsMarkup(E::ts('There are no proofs necessary.'))];
      $actions = [];
    }

    if ($this->hasReviewPermission($clearingProcessBundle->getFundingCase()->getPermissions())) {
      // @phpstan-ignore-next-line
      $keywords['properties']['comment'] = new JsonSchemaComment();
    }
    else {
      // Prevent adding a comment without permission
      // @phpstan-ignore-next-line
      $keywords['properties']['comment'] = new JsonSchemaNull();
    }

    $actionsEnum = array_keys($actions);
    if ([] === $actionsEnum) {
      // empty array is not allowed as enum
      $actionsEnum = [NULL];
    }
    // @phpstan-ignore-next-line
    $keywords['properties']['_action'] =
      new JsonSchemaString(['enum' => $actionsEnum]);
    $keywords['required'][] = '_action';

    $uiSchema = new JsonFormsGroup(E::ts('Clearing'), $elements);
    if ($this->actionsDeterminer->isEditAllowed(
      $clearingProcessBundle->getClearingProcess()->getFullStatus(),
      $clearingProcessBundle->getFundingCase()->getPermissions()
    )) {
      $uiSchema->setReadonly(TRUE);
    }

    return new JsonFormsForm(JsonSchema::fromArray($keywords), $uiSchema);
  }

}
