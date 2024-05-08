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
use Civi\Funding\ClearingProcess\Traits\HasClearingReviewPermissionTrait;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Form\JsonSchema\JsonSchemaComment;
use Civi\Funding\Util\ArrayUtil;
use Civi\RemoteTools\JsonForms\Control\JsonFormsSubmitButton;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
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
 *   file: string|null,
 *   receiptNumber: ?string,
 *   paymentDate: string,
 *   recipient: string,
 *   reason: string,
 *   amount: float,
 *   amountAdmitted: ?float,
 * }
 *
 * @phpstan-type clearingFormDataT array{
 *   _action: string,
 *   costItems?: array<int, array{records: list<clearingItemRecordT>}>,
 *   costItemsAmountAdmitted?: float,
 *   costItemsAmountRecorded?: float,
 *   resourcesItems?: array<int, array{records: list<clearingItemRecordT>}>,
 *   resourcesItemsAdmountAdmitted?: float,
 *   resourcesItemsAmountRecorded?: float,
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

  private ReceiptsFormGeneratorInterface $receiptsFormGenerator;

  private ReportFormFactoryInterface $reportFormFactory;

  public function __construct(
    ClearingActionsDeterminer $actionsDeterminer,
    ReceiptsFormGeneratorInterface $receiptsFormGenerator,
    ReportFormFactoryInterface $reportDataFormFactory
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->receiptsFormGenerator = $receiptsFormGenerator;
    $this->reportFormFactory = $reportDataFormFactory;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function generateForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    $receiptsForm = $this->receiptsFormGenerator->generateReceiptsForm($clearingProcessBundle);
    $reportForm = $this->reportFormFactory->createReportForm($clearingProcessBundle);

    $keywords = $receiptsForm->getJsonSchema()->toArray();
    if ([] !== $keywords) {
      $receiptsFormUiSchema = $receiptsForm->getUiSchema();
      if (NULL !== $reportForm->getReceiptsPrependUiSchema()) {
        // @phpstan-ignore-next-line
        $receiptsFormUiSchema['elements'] = array_merge(
          [$reportForm->getReceiptsPrependUiSchema()],
          // @phpstan-ignore-next-line
          $receiptsFormUiSchema['elements']
        );
      }
      if (NULL !== $reportForm->getReceiptsAppendUiSchema()) {
        // @phpstan-ignore-next-line
        $receiptsFormUiSchema['elements'] = array_merge(
        // @phpstan-ignore-next-line
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

      $actions = $this->actionsDeterminer->getActions(
        $clearingProcessBundle->getClearingProcess(),
        $clearingProcessBundle->getFundingCase()->getPermissions()
      );

      foreach ($actions as $name => $label) {
        $elements[] = new JsonFormsSubmitButton('#/properties/_action', $name, $label);
      }
    }
    else {
      $elements = [new JsonFormsMarkup('<p>' . E::ts('There are no receipts necessary.') . '</p>')];
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
    if (!$this->actionsDeterminer->isEditAllowed(
      $clearingProcessBundle->getClearingProcess(),
      $clearingProcessBundle->getFundingCase()->getPermissions()
    )) {
      $uiSchema->setReadonly(TRUE);
    }

    return new JsonFormsForm(JsonSchema::fromArray($keywords), $uiSchema);
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
