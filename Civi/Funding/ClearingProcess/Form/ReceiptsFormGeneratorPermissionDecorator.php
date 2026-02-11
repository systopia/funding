<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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
use Civi\Funding\ClearingProcess\ClearingCostItemManager;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaDataPointer;
use Webmozart\Assert\Assert;

final class ReceiptsFormGeneratorPermissionDecorator implements ReceiptsFormGeneratorInterface {

  private ReceiptsFormGeneratorInterface $receiptsFormGenerator;

  private ClearingActionsDeterminer $actionsDeterminer;

  private ClearingCostItemManager $costItemManager;

  private ClearingResourcesItemManager $resourcesItemManager;

  // @phpstan-ignore property.uninitialized
  private ClearingCostItemManager|ClearingResourcesItemManager $currentClearingItemManager;

  public function __construct(
    ReceiptsFormGeneratorInterface $receiptsFormGenerator,
    ClearingActionsDeterminer $actionsDeterminer,
    ClearingCostItemManager $costItemManager,
    ClearingResourcesItemManager $resourcesItemManager
  ) {
    $this->receiptsFormGenerator = $receiptsFormGenerator;
    $this->actionsDeterminer = $actionsDeterminer;
    $this->costItemManager = $costItemManager;
    $this->resourcesItemManager = $resourcesItemManager;
  }

  /**
   * @inheritDoc
   */
  public function generateReceiptsForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    $admittedValueChangeAllowed = $this->actionsDeterminer->isAdmittedValueChangeAllowed($clearingProcessBundle);
    $contentChangeAllowed = $this->actionsDeterminer->isContentChangeAllowed($clearingProcessBundle);

    $form = $this->receiptsFormGenerator->generateReceiptsForm($clearingProcessBundle);

    $jsonSchema = $form->getJsonSchema();

    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema|null $costItemsSchema */
    $costItemsSchema = $jsonSchema['properties']['costItems'] ?? NULL;
    if (NULL !== $costItemsSchema) {
      $this->currentClearingItemManager = $this->costItemManager;
      $this->handleItemsSchema($costItemsSchema, $admittedValueChangeAllowed, $contentChangeAllowed);
    }

    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema|null $resourcesItemsSchema */
    $resourcesItemsSchema = $jsonSchema['properties']['resourcesItems'] ?? NULL;
    if (NULL !== $resourcesItemsSchema) {
      $this->currentClearingItemManager = $this->resourcesItemManager;
      $this->handleItemsSchema($resourcesItemsSchema, $admittedValueChangeAllowed, $contentChangeAllowed);
    }

    return $form;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function handleItemsSchema(
    JsonSchema $costItemsSchema,
    bool $admittedValueChangeAllowed,
    bool $contentChangeAllowed
  ): void {
    Assert::same('object', $costItemsSchema['type']);
    $costItemsSchema['additionalProperties'] = FALSE;
    /** @var iterable<int|string, JsonSchema> $properties */
    // @phpstan-ignore varTag.nativeType
    $properties = $costItemsSchema['properties'];
    foreach ($properties as $dataKey => $property) {
      /** @var \Civi\RemoteTools\JsonSchema\JsonSchema|null $recordsSchema */
      $recordsSchema = $property['properties']['records'] ?? NULL;
      Assert::notNull($recordsSchema);
      if ('object' === $recordsSchema['type']) {
        $this->handleRecordsObjectSchema($recordsSchema, $admittedValueChangeAllowed, $contentChangeAllowed);
      }
      elseif ('array' === $recordsSchema['type']) {
        $this->handleRecordsArraySchema(
          $recordsSchema,
          (string) $dataKey,
          $admittedValueChangeAllowed,
          $contentChangeAllowed
        );
      }
      else {
        throw new \InvalidArgumentException(
          'Expected "object" or "array", got "' . print_r($recordsSchema['type'], TRUE) . '" as type for "records"'
        );
      }
    }
  }

  private function handleRecordsObjectSchema(
    JsonSchema $recordsSchema,
    bool $admittedValueChangeAllowed,
    bool $contentChangeAllowed
  ): void {
    $recordsSchema['additionalProperties'] = FALSE;
    /** @var iterable<int|string, JsonSchema> $properties */
    // @phpstan-ignore varTag.nativeType
    $properties = $recordsSchema['properties'];
    foreach ($properties as $clearingItemSchema) {
      $this->handleClearingItemSchema($clearingItemSchema, $admittedValueChangeAllowed, $contentChangeAllowed);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function handleRecordsArraySchema(
    JsonSchema $recordsSchema,
    string $dataKey,
    bool $admittedValueChangeAllowed,
    bool $contentChangeAllowed
  ): void {
    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $itemSchema */
    $itemSchema = $recordsSchema['items'];
    $this->handleClearingItemSchema($itemSchema, $admittedValueChangeAllowed, $contentChangeAllowed);

    if (!$contentChangeAllowed) {
      // @phpstan-ignore offsetAccess.nonOffsetAccessible, offsetAccess.nonOffsetAccessible
      $financePlanItemId = $itemSchema['properties']['_financePlanItemId']['const'];
      Assert::integer($financePlanItemId);
      /** @var int $financePlanItemId */

      $itemCount = $this->currentClearingItemManager->countByFinancePlanItemIdAndDataKey($financePlanItemId, $dataKey);
      $recordsSchema['minItems'] = $itemCount;
      $recordsSchema['maxItems'] = $itemCount;
    }
  }

  private function handleClearingItemSchema(
    JsonSchema $itemSchema,
    bool $admittedValueChangeAllowed,
    bool $contentChangeAllowed
  ): void {
    Assert::same('object', $itemSchema['type']);
    /** @var \Civi\RemoteTools\JsonSchema\JsonSchema&\ArrayAccess<string, JsonSchema> $properties */
    $properties = $itemSchema['properties'];

    $properties['_id']['readOnly'] = TRUE;
    $properties['_financePlanItemId']['readOnly'] = TRUE;

    if (!$contentChangeAllowed) {
      // Prevent null for _id.
      $properties['_id']['type'] = 'integer';
      // @phpstan-ignore argument.type, offsetAssign.valueType
      $itemSchema['required'] = array_merge($itemSchema['required'], ['_id']);

      /** @var \Civi\RemoteTools\JsonSchema\JsonSchema $property */
      foreach ($properties as $propertyName => $property) {
        if ('amountAdmitted' !== $propertyName) {
          $property['readOnly'] = TRUE;
        }
      }
    }

    if ($admittedValueChangeAllowed) {
      $properties['amountAdmitted']['default'] = new JsonSchemaDataPointer('1/amount');
    }
    else {
      $properties['amountAdmitted']['readOnly'] = TRUE;
      $properties['amountAdmitted']['default'] = NULL;
      if (!$contentChangeAllowed) {
        $itemSchema['readOnly'] = TRUE;
      }
    }
  }

}
