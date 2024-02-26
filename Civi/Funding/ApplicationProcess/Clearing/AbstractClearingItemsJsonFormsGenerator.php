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

namespace Civi\Funding\ApplicationProcess\Clearing;

use Civi\Core\Format;
use Civi\Funding\ApplicationProcess\Clearing\Container\ClearingItemsGroup;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\Control\JsonFormsValue;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonForms\JsonFormsMarkup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsCloseableGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTable;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTableRow;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaCalculate;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
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

  /**
   * @phpstan-var AbstractClearableItemsLoader<T>
   */
  private AbstractClearableItemsLoader $clearableItemsLoader;

  private ClearingGroupExtractor $clearingGroupExtractor;

  private Format $format;

  private ItemDetailsFormElementGenerator $itemDetailsFormElementGenerator;

  /**
   * @var array<int|string, \Civi\RemoteTools\JsonSchema\JsonSchema>
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

    foreach ($groups as $group) {
      $this->handleGroup($group, $applicationProcessBundle->getFundingProgram()->getCurrency(), $clearableItems);
    }

    if ([] === $this->properties) {
      return JsonFormsForm::newEmpty();
    }

    $jsonSchema = new JsonSchemaObject([
      $this->getPropertyKeyword() => new JsonSchemaObject(
        $this->properties,
        ['required' => array_map(fn ($key) => (string) $key, array_keys($this->properties))]
      ),
    ], ['required' => [$this->getPropertyKeyword()]]);
    $uiSchema = new JsonFormsGroup($this->getTitle(), $this->formElements);

    return new JsonFormsForm($jsonSchema, $uiSchema);
  }

  /**
   * @phpstan-param array<string, \Civi\Funding\ApplicationProcess\Clearing\Container\ClearableItems<T>> $clearableItems
   */
  private function handleGroup(
    ClearingItemsGroup $group,
    string $currency,
    array $clearableItems
  ): void {
    $groupElements = [];
    foreach ($group->elements as $scope => $applicationFormElement) {
      $applicationPropertySchema = $clearableItems[$scope]->propertySchema;
      $financePlanItemSchema = $clearableItems[$scope]->financePlanItemSchema;
      $items = $clearableItems[$scope]->items;

      foreach ($items as $index => $item) {
        $this->properties[$item->getId()] = new JsonSchemaObject([
          'amountRecordedTotal' => new JsonSchemaCalculate(
            'number',
            'round(sum(map(honorare, "value.amount")), 2)',
            ['honorare' => new JsonSchemaDataPointer('1/records')]
          ),
          'amountAdmittedTotal' => new JsonSchemaCalculate(
            'number',
            'round(sum(map(honorare, "value.amountAdmitted")), 2)',
            ['honorare' => new JsonSchemaDataPointer('1/records')]
          ),
          'records' => new JsonSchemaArray(
            new JsonSchemaObject([
              '_id' => new JsonSchemaInteger(['readOnly' => TRUE, 'default' => NULL], TRUE),
              'file' => new JsonSchemaString(['format' => 'uri']),
              'description' => new JsonSchemaString(),
              'amount' => new JsonSchemaMoney(),
              'amountAdmitted' => new JsonSchemaMoney(['readOnly' => TRUE, 'default' => 0]),
            ], ['required' => ['_id', 'description', 'amount']])
          ),
        ], ['required' => ['records']]);

        $itemLabel = $financePlanItemSchema->getKeywordValueAt('clearing/itemLabel');
        Assert::string($itemLabel);
        $groupElements[] = new JsonFormsTable(
          [
            E::ts('Item'),
            E::ts('Amount Approved'),
            E::ts('Amount Admitted in %1', [1 => $currency]),
            E::ts('Amount Recorded in %1', [1 => $currency]),
          ], [
            new JsonFormsTableRow([
              new JsonFormsMarkup($this->formatLabel($itemLabel, $index)),
              new JsonFormsMarkup($this->format->money($item->getAmount(), $currency)),
              new JsonFormsControl(sprintf(
                '#/properties/%s/properties/%s/properties/amountAdmittedTotal',
                $this->getPropertyKeyword(),
                $item->getId()
              ), ''),
              new JsonFormsControl(sprintf(
                '#/properties/%s/properties/%s/properties/amountRecordedTotal',
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

        $groupElements[] =
          new JsonFormsCloseableGroup(E::ts('Proofs'), [
            new JsonFormsArray(
              sprintf(
                '#/properties/%s/properties/%s/properties/records',
                $this->getPropertyKeyword(),
                $item->getId()
              ),
              '',
              NULL,
              [
                new JsonFormsValue('#/properties/_id'),
                new JsonFormsControl('#/properties/file', E::ts('Proof'), NULL, ['format' => 'file']),
                new JsonFormsControl('#/properties/description', E::ts('Description')),
                new JsonFormsControl('#/properties/amountAdmitted', E::ts('Amount Admitted in %1', [1 => $currency])),
                new JsonFormsControl('#/properties/amount', E::ts('Amount in %1', [1 => $currency])),
              ],
              ['addButtonLabel' => E::ts('Add Proof')]
            ),
          ]);
      }
    }

    if (NULL === $group->label) {
      $this->formElements = array_merge($this->formElements, $groupElements);
    }
    else {
      $this->formElements[] = new JsonFormsGroup($group->label, $groupElements);
    }
  }

  abstract protected function getPropertyKeyword(): string;

  abstract protected function getTitle(): string;

  private function formatLabel(string $itemLabel, int $index): string {
    return str_replace('{@pos}', (string) ($index + 1), $itemLabel);
  }

}
