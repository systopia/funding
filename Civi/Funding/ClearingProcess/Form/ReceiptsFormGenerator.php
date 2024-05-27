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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ClearingProcess\Form\CostItem\ClearingCostItemsJsonFormsGenerator;
use Civi\Funding\ClearingProcess\Form\ResourcesItem\ClearingResourcesItemsJsonFormsGenerator;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Util\ArrayUtil;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use CRM_Funding_ExtensionUtil as E;

final class ReceiptsFormGenerator implements ReceiptsFormGeneratorInterface {

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormCreateHandlerInterface $applicationFormCreateHandler;

  private ClearingCostItemsJsonFormsGenerator $clearingCostItemsJsonFormsGenerator;

  private ClearingResourcesItemsJsonFormsGenerator $clearingResourcesItemsJsonFormsGenerator;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormCreateHandlerInterface $applicationFormCreateHandler,
    ClearingCostItemsJsonFormsGenerator $clearingCostItemsJsonFormsGenerator,
    ClearingResourcesItemsJsonFormsGenerator $clearingResourcesItemsJsonFormsGenerator
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->applicationFormCreateHandler = $applicationFormCreateHandler;
    $this->clearingCostItemsJsonFormsGenerator = $clearingCostItemsJsonFormsGenerator;
    $this->clearingResourcesItemsJsonFormsGenerator = $clearingResourcesItemsJsonFormsGenerator;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function generateReceiptsForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
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

    if ([] === $keywords) {
      return JsonFormsForm::newEmpty();
    }

    /** @phpstan-var array<string, mixed> $properties */
    $properties = &$keywords['properties'];
    $receiptsElements = [
      $costItemsForm->getUiSchema(),
      $resourcesItemsForm->getUiSchema(),
    ];

    if (isset($properties['costItemsAmountRecorded']) && isset($properties['resourcesItemsAmountRecorded'])) {
      $properties['amountCleared'] = new JsonSchemaCalculate(
        'number', 'round(recordedCosts - recordedResources, 2)', [
          'recordedCosts' => new JsonSchemaDataPointer('1/costItemsAmountRecorded', 0),
          'recordedResources' => new JsonSchemaDataPointer('1/resourcesItemsAmountRecorded', 0),
        ]
      );
      $receiptsElements[] = new JsonFormsGroup(
        E::ts('Amount Cleared in %1', [1 => $clearingProcessBundle->getFundingProgram()->getCurrency()]),
        [new JsonFormsControl('#/properties/amountCleared', '')]
      );
    }

    if (isset($properties['costItemsAmountAdmitted']) && isset($properties['resourcesItemsAmountAdmitted'])) {
      $properties['amountAdmitted'] = new JsonSchemaCalculate(
        'number', 'round(admittedCosts - admittedResources, 2)', [
          'admittedCosts' => new JsonSchemaDataPointer('1/costItemsAmountAdmitted', 0),
          'admittedResources' => new JsonSchemaDataPointer('1/resourcesItemsAmountAdmitted', 0),
        ]
      );
      $receiptsElements[] = new JsonFormsGroup(
        E::ts('Amount Admitted in %1', [1 => $clearingProcessBundle->getFundingProgram()->getCurrency()]),
        [new JsonFormsControl('#/properties/amountAdmitted', '')]
      );
    }

    $uiSchema = new JsonFormsCategory(E::ts('Receipts'), [new JsonFormsGroup(E::ts('Receipts'), $receiptsElements)]);

    return new JsonFormsForm(JsonSchema::fromArray($keywords), $uiSchema);
  }

}
