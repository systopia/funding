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

namespace Civi\Funding\ClearingProcess;

use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Clearing\CostItem\ClearingCostItemsJsonFormsGenerator;
use Civi\Funding\ApplicationProcess\Clearing\ResourcesItem\ClearingResourcesItemsJsonFormsGenerator;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Util\ArrayUtil;
use Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use CRM_Funding_ExtensionUtil as E;

final class ClearingFormsGenerator {

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
      $resourcesItemsForm->getJsonSchema()->toArray()
    ) + ['type' => 'object'];

    $categories = [];
    if ([] !== $keywords) {
      $keywords['properties']['_action'] = new JsonSchemaString(['enum' => ['save', 'apply']]);
      $keywords['required'][] = '_action';
      $categories[] = new JsonFormsCategory(E::ts('Proofs'), [
        $costItemsForm->getUiSchema(),
        $resourcesItemsForm->getUiSchema(),
      ]);
    }

    $jsonSchema = JsonSchema::fromArray($keywords);

    if ([] !== $categories) {
      $elements = [
        new JsonFormsCategorization($categories),
        new JsonFormsSubmitButton('#/properties/_action', 'save', E::ts('Save')),
        new JsonFormsSubmitButton('#/properties/_action', 'request_review', E::ts('Request Review')),
      ];
    }
    else {
      $elements = [new JsonFormsMarkup(E::ts('There are no proofs necessary.'))];
    }

    return new JsonFormsForm(
      $jsonSchema, new JsonFormsGroup(E::ts('Clearing'), $elements)
    );
  }

}
