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

use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Form\JsonSchema\JsonSchemaComment;
use Civi\Funding\Translation\FormTranslatorInterface;
use Civi\Funding\Util\ArrayUtil;
use Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategorization;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCategory;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaNull;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type clearingItemsT from ReceiptsFormGeneratorInterface
 *
 * @phpstan-type clearingFormDataT array{
 *   _action: string,
 *   costItems?: clearingItemsT,
 *   resourcesItems?: clearingItemsT,
 *   reportData?: array<string, mixed>,
 *   comment?: array{text: string, type: 'internal'|'external'},
 * }
 *
 * This class generates a JSON Forms specification that has a JSON schema that
 * validates the data specified in clearingFormDataT. (For displaying purposes
 * the schema might have additional properties.)
 */
final class ClearingFormGenerator {

  private ClearingActionsDeterminer $actionsDeterminer;

  private FormTranslatorInterface $formTranslator;

  private ReceiptsFormGeneratorInterface $receiptsFormGenerator;

  private ReportFormFactoryInterface $reportFormFactory;

  public function __construct(
    ClearingActionsDeterminer $actionsDeterminer,
    FormTranslatorInterface $formTranslator,
    ReceiptsFormGeneratorInterface $receiptsFormGenerator,
    ReportFormFactoryInterface $reportDataFormFactory
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->formTranslator = $formTranslator;
    $this->receiptsFormGenerator = $receiptsFormGenerator;
    $this->reportFormFactory = $reportDataFormFactory;
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public function generateForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
  // phpcs:enable
    $receiptsForm = $this->receiptsFormGenerator->generateReceiptsForm($clearingProcessBundle);
    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $receiptsProperties */
    $receiptsProperties = $receiptsForm->getJsonSchema()['properties']
      ??= new JsonSchemaObject([], ['additionalProperties' => FALSE]);
    $receiptsProperties['costItems'] ??= new JsonSchemaObject([], ['additionalProperties' => FALSE]);
    $receiptsProperties['resourcesItems'] ??= new JsonSchemaObject([], ['additionalProperties' => FALSE]);
    $reportForm = $this->reportFormFactory->createReportForm($clearingProcessBundle);
    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $reportProperties */
    $reportProperties = $reportForm->getJsonSchema()['properties']
      ??= new JsonSchemaObject([], ['additionalProperties' => FALSE]);
    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $reportDataSchema */
    $reportDataSchema = $reportProperties['reportData']
      ??= new JsonSchemaObject([], ['additionalProperties' => FALSE]);
    if (!$this->actionsDeterminer->isContentChangeAllowed($clearingProcessBundle)) {
      $reportDataSchema['readOnly'] = TRUE;
    }

    $keywords = $receiptsForm->getJsonSchema()->toArray();
    if ([] !== $keywords) {
      $receiptsFormUiSchema = $receiptsForm->getUiSchema();
      if (NULL !== $reportForm->getReceiptsPrependUiSchema()) {
        $receiptsFormUiSchema['elements'] = array_merge(
          [$reportForm->getReceiptsPrependUiSchema()],
          // @phpstan-ignore argument.type
          $receiptsFormUiSchema['elements']
        );
      }
      if (NULL !== $reportForm->getReceiptsAppendUiSchema()) {
        $receiptsFormUiSchema['elements'] = array_merge(
          // @phpstan-ignore argument.type
          $receiptsFormUiSchema['elements'],
          [$reportForm->getReceiptsAppendUiSchema()]
        );
      }

      $categories = $this->toCategories($receiptsFormUiSchema, E::ts('Receipts'));
    }
    else {
      $categories = [];
    }

    if ([] !== $reportForm->getJsonSchema()->getKeywords()) {
      $keywords = ArrayUtil::mergeRecursive($keywords, $reportForm->getJsonSchema()->toArray());
      $categories = array_merge($this->toCategories($reportForm->getUiSchema(), E::ts('Report')), $categories);
    }

    if ([] !== $categories) {
      $elements = [new JsonFormsCategorization($categories)];

      $actions = $this->actionsDeterminer->getActions($clearingProcessBundle);

      foreach ($actions as $name => $label) {
        $elements[] = new JsonFormsSubmitButton('#/properties/_action', $name, $label);
      }
    }
    else {
      $elements = [new JsonFormsMarkup('<p>' . E::ts('There are no receipts necessary.') . '</p>')];
      $actions = [];
    }

    if ($this->actionsDeterminer->isActionAllowed('add-comment', $clearingProcessBundle)) {
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
    if (!$this->actionsDeterminer->isEditAllowed($clearingProcessBundle)) {
      $uiSchema->setReadonly(TRUE);
    }

    $keywords['additionalProperties'] = FALSE;

    $form = new JsonFormsForm(JsonSchema::fromArray($keywords), $uiSchema);
    $this->formTranslator->translateForm(
      $form,
      $clearingProcessBundle->getFundingProgram(),
      $clearingProcessBundle->getFundingCaseType()
    );

    return $form;
  }

  /**
   * @phpstan-return list<JsonFormsElement>
   */
  private function toCategories(JsonFormsElement $element, string $fallbackLabel): array {
    if ($element->getKeywordValue('type') === 'Categorization') {
      // @phpstan-ignore-next-line
      return $element->getKeywordValue('elements');
    }

    if ($element->getKeywordValue('type') === 'Category') {
      return [$element];
    }

    $reportCategoryLabel = $element['label'] ?? $fallbackLabel;
    Assert::string($reportCategoryLabel);

    return [new JsonFormsCategory($reportCategoryLabel, [$element])];
  }

}
