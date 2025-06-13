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

use Civi\Core\Format;
use Civi\Funding\ClearingProcess\Form\Container\ClearableItems;
use Civi\Funding\ClearingProcess\Form\Container\ClearingItemsGroup;
use Civi\Funding\ClearingProcess\Traits\HasClearingReviewPermissionTrait;
use Civi\Funding\Entity\AbstractFinancePlanItemEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCloseableGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTable;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTableRow;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Civi\RemoteTools\JsonSchema\JsonSchemaDate;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @template T of \Civi\Funding\Entity\AbstractFinancePlanItemEntity
 */
abstract class AbstractClearingItemsJsonFormsGenerator {

  use HasClearingReviewPermissionTrait;

  /**
   * @phpstan-var AbstractClearableItemsLoader<T>
   */
  private AbstractClearableItemsLoader $clearableItemsLoader;

  private ClearingGroupExtractor $clearingGroupExtractor;

  private Format $format;

  private ItemDetailsFormElementGenerator $itemDetailsFormElementGenerator;

  /**
   * @var array<int, \Civi\RemoteTools\JsonSchema\JsonSchema>
   *   Mapping of clearable finance plan item ID to JsonSchema.
   */
  private array $properties = [];

  /**
   * @phpstan-var list<\Civi\RemoteTools\JsonForms\JsonFormsElement>
   */
  private array $formElements = [];

  /**
   * @phpstan-param AbstractClearableItemsLoader<T> $clearableItemsLoader
   */
  public function __construct(
    AbstractClearableItemsLoader $clearableItemsLoader,
    ClearingGroupExtractor $clearingGroupExtractor,
    Format $format,
    ItemDetailsFormElementGenerator $itemDetailsFormElementGenerator
  ) {
    $this->clearableItemsLoader = $clearableItemsLoader;
    $this->clearingGroupExtractor = $clearingGroupExtractor;
    $this->format = $format;
    $this->itemDetailsFormElementGenerator = $itemDetailsFormElementGenerator;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function generate(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    JsonFormsFormInterface $applicationForm
  ): JsonFormsFormInterface {
    $this->properties = [];
    $this->formElements = [];

    $clearableItems = $this->clearableItemsLoader->getClearableItems(
      $applicationProcessBundle,
      $applicationForm->getJsonSchema()
    );

    $groups = $this->clearingGroupExtractor->extractGroups(
      $applicationForm->getUiSchema(),
      array_keys($clearableItems)
    );

    $hasReviewPermission = $this->hasReviewPermission($applicationProcessBundle->getFundingCase()->getPermissions());
    foreach ($groups as $group) {
      $this->handleGroup(
        $group,
        $applicationProcessBundle->getFundingProgram()->getCurrency(),
        $clearableItems,
        $hasReviewPermission
      );
    }

    if ([] === $this->properties) {
      return JsonFormsForm::newEmpty();
    }

    $amountRecordedOverallVariables = [];
    $amountAdmittedOverallVariables = [];
    $itemIds = array_keys($this->properties);
    foreach ($itemIds as $itemId) {
      $recordedVariableName = sprintf('item%dRecorded', $itemId);
      $amountRecordedOverallVariables[$recordedVariableName] = new JsonSchemaDataPointer(
        sprintf('1/%s/%d/amountRecordedTotal', $this->getPropertyKeyword(), $itemId),
        0
      );

      $admittedVariableName = sprintf('item%dRecorded', $itemId);
      $amountAdmittedOverallVariables[$admittedVariableName] = new JsonSchemaDataPointer(
        sprintf('1/%s/%d/amountAdmittedTotal', $this->getPropertyKeyword(), $itemId),
        0
      );
    }

    $jsonSchema = new JsonSchemaObject([
      $this->getPropertyKeyword() => new JsonSchemaObject($this->properties),
      $this->getPropertyKeyword() . 'AmountRecorded' => new JsonSchemaCalculate(
        'number',
        'round(' . implode('+', array_keys($amountRecordedOverallVariables)) . ', 2)',
        $amountRecordedOverallVariables
      ),
      $this->getPropertyKeyword() . 'AmountAdmitted' => new JsonSchemaCalculate(
        'number',
        'round(' . implode('+', array_keys($amountAdmittedOverallVariables)) . ', 2)',
        $amountAdmittedOverallVariables
      ),
    ], ['required' => [$this->getPropertyKeyword()]]);

    $amountApprovedOverall = array_reduce(
      $clearableItems,
      fn (float $sum, ClearableItems $items) => round($sum + array_reduce(
        $items->items,
        fn(float $sum, AbstractFinancePlanItemEntity $item) => $sum + $item->getAmount(),
          0.0
      ), 2),
      0.0
    );
    $currency = $applicationProcessBundle->getFundingProgram()->getCurrency();
    $this->formElements[] = new JsonFormsGroup(E::ts('Overall'), [
      new JsonFormsTable([
        E::ts('Amount Approved'),
        E::ts('Amount Recorded in %1', [1 => $currency]),
        E::ts('Amount Admitted in %1', [1 => $currency]),
      ], [
        new JsonFormsTableRow([
          new JsonFormsMarkup($this->format->money($amountApprovedOverall, $currency)),
          new JsonFormsControl(
            sprintf('#/properties/%sAmountRecorded', $this->getPropertyKeyword()),
            ''
          ),
          new JsonFormsControl(
            sprintf('#/properties/%sAmountAdmitted', $this->getPropertyKeyword()),
            ''
          ),
        ]),
      ]),
    ]);
    $uiSchema = new JsonFormsGroup($this->getTitle(), $this->formElements);

    return new JsonFormsForm($jsonSchema, $uiSchema);
  }

  /**
   * @phpstan-param array<string, \Civi\Funding\ClearingProcess\Form\Container\ClearableItems<T>> $clearableItems
   */
  private function handleGroup(
    ClearingItemsGroup $group,
    string $currency,
    array $clearableItems,
    bool $hasReviewPermission
  ): void {
    $groupElements = [];
    foreach ($group->elements as $scope => $applicationFormElement) {
      $applicationPropertySchema = $clearableItems[$scope]->propertySchema;
      $financePlanItemSchema = $clearableItems[$scope]->financePlanItemSchema;
      $items = $clearableItems[$scope]->items;

      foreach ($items as $index => $item) {
        $this->properties[$item->getId()] = new JsonSchemaObject([
          'records' => new JsonSchemaArray(
            new JsonSchemaObject([
              '_id' => new JsonSchemaInteger(['readOnly' => TRUE, 'default' => NULL], TRUE),
              'file' => new JsonSchemaString(['format' => 'uri', 'default' => NULL], TRUE),
              'receiptNumber' => new JsonSchemaString(['maxlength' => 255], TRUE),
              'receiptDate' => new JsonSchemaDate([], TRUE),
              'paymentDate' => new JsonSchemaDate(),
              'recipient' => new JsonSchemaString(['maxlength' => 255]),
              'reason' => new JsonSchemaString(['maxlength' => 255]),
              'amount' => new JsonSchemaMoney(),
              'amountAdmitted' => new JsonSchemaMoney([
                'readOnly' => !$hasReviewPermission,
                'default' => $hasReviewPermission ? new JsonSchemaDataPointer('1/amount') : NULL,
              ], TRUE),
            ], ['required' => ['paymentDate', 'recipient', 'reason', 'amount']])
          ),
          'amountRecordedTotal' => new JsonSchemaCalculate(
            'number',
            'round(sum(map(records, "value.amount")), 2)',
            ['records' => new JsonSchemaDataPointer('1/records')],
            NULL,
            ['default' => 0]
          ),
          'amountAdmittedTotal' => new JsonSchemaCalculate(
            'number',
            // With Symfony Expression Language 6.2 we'd use '??' instead of '?:'
            // Though as long as we support PHP 7.4 we have to keep '?:'.
            'round(sum(map(records, "value.amountAdmitted ?: 0")), 2)',
            ['records' => new JsonSchemaDataPointer('1/records')],
            NULL,
            ['default' => 0]
          ),
        ], ['required' => ['records']]);

        $itemLabel = $financePlanItemSchema->getKeywordValueAt('clearing/itemLabel');
        Assert::string($itemLabel);

        // If the group label is NULL, it contains only one item and its label
        // shall be used as group label.
        $group->label ??= $itemLabel;

        $groupElements[] = new JsonFormsTable(
          [
            E::ts('Item'),
            E::ts('Amount Approved'),
            E::ts('Amount Recorded in %1', [1 => $currency]),
            E::ts('Amount Admitted in %1', [1 => $currency]),
          ], [
            new JsonFormsTableRow([
              new JsonFormsMarkup($this->formatLabel($itemLabel, $index)),
              new JsonFormsMarkup($this->format->money($item->getAmount(), $currency)),
              new JsonFormsControl(sprintf(
                '#/properties/%s/properties/%s/properties/amountRecordedTotal',
                $this->getPropertyKeyword(),
                $item->getId()
              ), ''),
              new JsonFormsControl(sprintf(
                '#/properties/%s/properties/%s/properties/amountAdmittedTotal',
                $this->getPropertyKeyword(),
                $item->getId()
              ), ''),
            ]),
          ]
        );

        if ('array' === $applicationPropertySchema->getKeywordValue('type')) {
          $groupElements[] = $this->itemDetailsFormElementGenerator->generateDetailsElement(
            $applicationPropertySchema,
            $financePlanItemSchema,
            $applicationFormElement,
            $item->getProperties()
          );
        }

        /** @var string $recipientLabel */
        $recipientLabel = $financePlanItemSchema['clearing']['recipientLabel'] ?? E::ts('Payment Recipient');

        $groupElements[] =
          new JsonFormsCloseableGroup(E::ts('Receipts'), [
            new JsonFormsArray(
              sprintf(
                '#/properties/%s/properties/%s/properties/records',
                $this->getPropertyKeyword(),
                $item->getId()
              ),
              '',
              NULL,
              // \u{200B} is used to allow line break at this position.
              [
                new JsonFormsHidden('#/properties/_id', ['internal' => TRUE]),
                new JsonFormsControl('#/properties/file', E::ts('Receipt'), NULL, ['format' => 'file']),
                new JsonFormsControl('#/properties/receiptNumber', E::ts('Receipt Number')),
                new JsonFormsControl('#/properties/receiptDate', E::ts('Receipt Date')),
                new JsonFormsControl(
                  '#/properties/paymentDate',
                  str_replace('/', "/\u{200B}", E::ts('Payment/Posting Date'))
                ),
                new JsonFormsControl('#/properties/recipient', $recipientLabel),
                new JsonFormsControl(
                  '#/properties/reason',
                  str_replace('/', "/\u{200B}", E::ts('Reason for Payment/Payment Reference'))
                ),
                new JsonFormsControl('#/properties/amount', E::ts('Amount in %1', [1 => $currency])),
                new JsonFormsControl('#/properties/amountAdmitted', E::ts('Amount Admitted in %1', [1 => $currency])),
              ],
              ['addButtonLabel' => E::ts('Add Receipt')]
            ),
          ]);
      }
    }

    Assert::string($group->label);
    $this->formElements[] = new JsonFormsGroup($group->label, $groupElements);
  }

  abstract protected function getPropertyKeyword(): string;

  abstract protected function getTitle(): string;

  private function formatLabel(string $itemLabel, int $index): string {
    return str_replace('{@pos}', (string) ($index + 1), $itemLabel);
  }

}
