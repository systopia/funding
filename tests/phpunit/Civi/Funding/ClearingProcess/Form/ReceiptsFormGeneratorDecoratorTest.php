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
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\JsonFormsForm;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorPermissionDecorator
 */
final class ReceiptsFormGeneratorDecoratorTest extends TestCase {

  private ClearingActionsDeterminer&MockObject $actionsDeterminerMock;

  private ClearingCostItemManager&MockObject $clearingCostItemMangerMock;

  private ClearingResourcesItemManager&MockObject $clearingResourcesItemManagerMock;

  private ReceiptsFormGeneratorPermissionDecorator $decorator;

  private ReceiptsFormGeneratorInterface&MockObject $formGeneratorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->formGeneratorMock = $this->createMock(ReceiptsFormGeneratorInterface::class);
    $this->actionsDeterminerMock = $this->createMock(ClearingActionsDeterminer::class);
    $this->clearingCostItemMangerMock = $this->createMock(ClearingCostItemManager::class);
    $this->clearingResourcesItemManagerMock = $this->createMock(ClearingResourcesItemManager::class);
    $this->decorator = new ReceiptsFormGeneratorPermissionDecorator(
      $this->formGeneratorMock,
      $this->actionsDeterminerMock,
      $this->clearingCostItemMangerMock,
      $this->clearingResourcesItemManagerMock
    );
  }

  /**
   * @param "costItems"|"resourcesItems" $itemType
   *
   * @dataProvider provideItemTypeAndRecordKey
   */
  public function testGenerateReceiptsForm(string $itemType, ?string $recordKey): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $this->actionsDeterminerMock->expects(static::once())->method('isContentChangeAllowed')
      ->with($clearingProcessBundle)
      ->willReturn(FALSE);

    $this->actionsDeterminerMock->expects(static::once())->method('isAdmittedValueChangeAllowed')
      ->with($clearingProcessBundle)
      ->willReturn(FALSE);

    $financePlanItemId = 12;
    $clearingItemCount = 2;

    if (NULL === $recordKey) {
      if ('costItems' === $itemType) {
        $this->clearingCostItemMangerMock->expects(static::once())->method('countByFinancePlanItemIdAndDataKey')
          ->with($financePlanItemId, 'financePlanItemAbc')
          ->willReturn($clearingItemCount);

        $this->clearingResourcesItemManagerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');
      }
      else {
        $this->clearingCostItemMangerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');

        $this->clearingResourcesItemManagerMock->expects(static::once())->method('countByFinancePlanItemIdAndDataKey')
          ->with($financePlanItemId, 'financePlanItemAbc')
          ->willReturn($clearingItemCount);
      }
    }
    else {
      $this->clearingCostItemMangerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');
      $this->clearingResourcesItemManagerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');
    }

    $this->formGeneratorMock->expects(static::once())->method('generateReceiptsForm')
      ->with($clearingProcessBundle)
      ->willReturn($this->createJsonForms($itemType, $recordKey, $financePlanItemId));

    $item = [
      'type' => 'object',
      'properties' => [
        '_id' => [
          'type' => 'integer',
          'readOnly' => TRUE,
          'default' => NULL,
        ],
        '_financePlanItemId' => [
          'type' => 'integer',
          'readOnly' => TRUE,
          'const' => $financePlanItemId,
          'default' => $financePlanItemId,
        ],
        'file' => [
          'type' => ['string', 'null'],
          'format' => 'uri',
          'readOnly' => TRUE,
          'default' => NULL,
        ],
        'receiptNumber' => [
          'type' => ['string', 'null'],
          'readOnly' => TRUE,
          'maxlength' => 255,
        ],
        'receiptDate' => [
          'type' => ['string', 'null'],
          'readOnly' => TRUE,
          'format' => 'date',
        ],
        'paymentDate' => [
          'type' => 'string',
          'readOnly' => TRUE,
          'format' => 'date',
        ],
        'recipient' => [
          'type' => 'string',
          'readOnly' => TRUE,
          'maxlength' => 255,
        ],
        'reason' => [
          'type' => 'string',
          'readOnly' => TRUE,
          'maxlength' => 255,
        ],
        'amount' => [
          'type' => 'number',
          'readOnly' => TRUE,
          'precision' => 2,
        ],
        'amountAdmitted' => [
          'type' => ['number', 'null'],
          'precision' => 2,
          'readOnly' => TRUE,
          'default' => NULL,
        ],
      ],
      'required' => ['_financePlanItemId', 'paymentDate', 'recipient', 'reason', 'amount', '_id'],
    ];

    if (NULL === $recordKey) {
      $records = [
        'type' => 'array',
        'minItems' => $clearingItemCount,
        'maxItems' => $clearingItemCount,
        'items' => $item,
      ];
    }
    else {
      $records = [
        'type' => 'object',
        'additionalProperties' => FALSE,
        'properties' => [
          $recordKey => $item,
        ],
      ];
    }

    $expected = [
      'type' => 'object',
      'properties' => [
        $itemType => [
          'type' => 'object',
          'additionalProperties' => FALSE,
          'properties' => [
            'financePlanItemAbc' => [
              'type' => 'object',
              'properties' => [
                'records' => $records,
              ],
              'required' => ['records'],
            ],
          ],
        ],
      ],
      'required' => [$itemType],
    ];

    static::assertEquals(
      $expected,
      $this->decorator->generateReceiptsForm($clearingProcessBundle)->getJsonSchema()->toArray()
    );
  }

  /**
   * @param "costItems"|"resourcesItems" $itemType
   *
   * @dataProvider provideItemTypeAndRecordKey
   */
  public function testGenerateReceiptsFormAdmittedValueChangeAllowed(string $itemType, ?string $recordKey): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();

    $this->actionsDeterminerMock->expects(static::once())->method('isContentChangeAllowed')
      ->with($clearingProcessBundle)
      ->willReturn(FALSE);

    $this->actionsDeterminerMock->expects(static::once())->method('isAdmittedValueChangeAllowed')
      ->with($clearingProcessBundle)
      ->willReturn(TRUE);

    $financePlanItemId = 12;
    $clearingItemCount = 2;

    if (NULL === $recordKey) {
      if ('costItems' === $itemType) {
        $this->clearingCostItemMangerMock->expects(static::once())->method('countByFinancePlanItemIdAndDataKey')
          ->with($financePlanItemId, 'financePlanItemAbc')
          ->willReturn($clearingItemCount);

        $this->clearingResourcesItemManagerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');
      }
      else {
        $this->clearingCostItemMangerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');

        $this->clearingResourcesItemManagerMock->expects(static::once())->method('countByFinancePlanItemIdAndDataKey')
          ->with($financePlanItemId, 'financePlanItemAbc')
          ->willReturn($clearingItemCount);
      }
    }
    else {
      $this->clearingCostItemMangerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');
      $this->clearingResourcesItemManagerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');
    }

    $this->formGeneratorMock->expects(static::once())->method('generateReceiptsForm')
      ->with($clearingProcessBundle)
      ->willReturn($this->createJsonForms($itemType, $recordKey, $financePlanItemId));

    $item = [
      'type' => 'object',
      'properties' => [
        '_id' => [
          'type' => 'integer',
          'readOnly' => TRUE,
          'default' => NULL,
        ],
        '_financePlanItemId' => [
          'type' => 'integer',
          'readOnly' => TRUE,
          'const' => $financePlanItemId,
          'default' => $financePlanItemId,
        ],
        'file' => [
          'type' => ['string', 'null'],
          'format' => 'uri',
          'readOnly' => TRUE,
          'default' => NULL,
        ],
        'receiptNumber' => [
          'type' => ['string', 'null'],
          'readOnly' => TRUE,
          'maxlength' => 255,
        ],
        'receiptDate' => [
          'type' => ['string', 'null'],
          'readOnly' => TRUE,
          'format' => 'date',
        ],
        'paymentDate' => [
          'type' => 'string',
          'readOnly' => TRUE,
          'format' => 'date',
        ],
        'recipient' => [
          'type' => 'string',
          'readOnly' => TRUE,
          'maxlength' => 255,
        ],
        'reason' => [
          'type' => 'string',
          'readOnly' => TRUE,
          'maxlength' => 255,
        ],
        'amount' => [
          'type' => 'number',
          'readOnly' => TRUE,
          'precision' => 2,
        ],
        'amountAdmitted' => [
          'type' => ['number', 'null'],
          'precision' => 2,
          'default' => ['$data' => '1/amount'],
        ],
      ],
      'required' => ['_financePlanItemId', 'paymentDate', 'recipient', 'reason', 'amount', '_id'],
    ];

    if (NULL === $recordKey) {
      $records = [
        'type' => 'array',
        'minItems' => $clearingItemCount,
        'maxItems' => $clearingItemCount,
        'items' => $item,
      ];
    }
    else {
      $records = [
        'type' => 'object',
        'additionalProperties' => FALSE,
        'properties' => [
          $recordKey => $item,
        ],
      ];
    }

    $expected = [
      'type' => 'object',
      'properties' => [
        $itemType => [
          'type' => 'object',
          'additionalProperties' => FALSE,
          'properties' => [
            'financePlanItemAbc' => [
              'type' => 'object',
              'properties' => [
                'records' => $records,
              ],
              'required' => ['records'],
            ],
          ],
        ],
      ],
      'required' => [$itemType],
    ];

    static::assertEquals(
      $expected,
      $this->decorator->generateReceiptsForm($clearingProcessBundle)->getJsonSchema()->toArray()
    );
  }

  /**
   * @param "costItems"|"resourcesItems" $itemType
   *
   * @dataProvider provideItemTypeAndRecordKey
   */
  public function testGenerateReceiptsFormContentChangeAllowed(string $itemType, ?string $recordKey): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $this->actionsDeterminerMock->expects(static::once())->method('isContentChangeAllowed')
      ->with($clearingProcessBundle)
      ->willReturn(TRUE);

    $this->actionsDeterminerMock->expects(static::once())->method('isAdmittedValueChangeAllowed')
      ->with($clearingProcessBundle)
      ->willReturn(FALSE);

    $financePlanItemId = 12;

    $this->clearingCostItemMangerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');

    $this->clearingResourcesItemManagerMock->expects(static::never())->method('countByFinancePlanItemIdAndDataKey');

    $this->formGeneratorMock->expects(static::once())->method('generateReceiptsForm')
      ->with($clearingProcessBundle)
      ->willReturn($this->createJsonForms($itemType, $recordKey, $financePlanItemId));

    $item = [
      'type' => 'object',
      'properties' => [
        '_id' => [
          'type' => ['integer', 'null'],
          'readOnly' => TRUE,
          'default' => NULL,
        ],
        '_financePlanItemId' => [
          'type' => 'integer',
          'readOnly' => TRUE,
          'const' => $financePlanItemId,
          'default' => $financePlanItemId,
        ],
        'file' => [
          'type' => ['string', 'null'],
          'format' => 'uri',
          'default' => NULL,
        ],
        'receiptNumber' => [
          'type' => ['string', 'null'],
          'maxlength' => 255,
        ],
        'receiptDate' => [
          'type' => ['string', 'null'],
          'format' => 'date',
        ],
        'paymentDate' => [
          'type' => 'string',
          'format' => 'date',
        ],
        'recipient' => [
          'type' => 'string',
          'maxlength' => 255,
        ],
        'reason' => [
          'type' => 'string',
          'maxlength' => 255,
        ],
        'amount' => [
          'type' => 'number',
          'precision' => 2,
        ],
        'amountAdmitted' => [
          'type' => ['number', 'null'],
          'precision' => 2,
          'readOnly' => TRUE,
          'default' => NULL,
        ],
      ],
      'required' => ['_financePlanItemId', 'paymentDate', 'recipient', 'reason', 'amount'],
    ];

    if (NULL === $recordKey) {
      $records = [
        'type' => 'array',
        'items' => $item,
      ];
    }
    else {
      $records = [
        'type' => 'object',
        'additionalProperties' => FALSE,
        'properties' => [
          $recordKey => $item,
        ],
      ];
    }

    $expected = [
      'type' => 'object',
      'properties' => [
        $itemType => [
          'type' => 'object',
          'additionalProperties' => FALSE,
          'properties' => [
            'financePlanItemAbc' => [
              'type' => 'object',
              'properties' => [
                'records' => $records,
              ],
              'required' => ['records'],
            ],
          ],
        ],
      ],
      'required' => [$itemType],
    ];

    static::assertEquals(
      $expected,
      $this->decorator->generateReceiptsForm($clearingProcessBundle)->getJsonSchema()->toArray()
    );
  }

  /**
   * @phpstan-return iterable<array{"costItems"|"resourcesItems", string|null}>
   */
  public function provideItemTypeAndRecordKey(): iterable {
    yield ['costItems', NULL];
    yield ['costItems', 'record1'];
    yield ['resourcesItems', NULL];
    yield ['resourcesItems', 'record1'];
  }

  private function createJsonForms(string $itemType, ?string $recordKey, int $financePlanItemId): JsonFormsForm {
    $item = [
      'type' => 'object',
      'properties' => [
        '_id' => [
          'type' => ['integer', 'null'],
          'default' => NULL,
        ],
        '_financePlanItemId' => [
          'type' => 'integer',
          'const' => $financePlanItemId,
          'default' => $financePlanItemId,
        ],
        'file' => [
          'type' => ['string', 'null'],
          'format' => 'uri',
          'default' => NULL,
        ],
        'receiptNumber' => [
          'type' => ['string', 'null'],
          'maxlength' => 255,
        ],
        'receiptDate' => [
          'type' => ['string', 'null'],
          'format' => 'date',
        ],
        'paymentDate' => [
          'type' => 'string',
          'format' => 'date',
        ],
        'recipient' => [
          'type' => 'string',
          'maxlength' => 255,
        ],
        'reason' => [
          'type' => 'string',
          'maxlength' => 255,
        ],
        'amount' => [
          'type' => 'number',
          'precision' => 2,
        ],
        'amountAdmitted' => [
          'type' => ['number', 'null'],
          'precision' => 2,
        ],
      ],
      'required' => ['_financePlanItemId', 'paymentDate', 'recipient', 'reason', 'amount'],
    ];

    if (NULL === $recordKey) {
      $records = [
        'type' => 'array',
        'items' => $item,
      ];
    }
    else {
      $records = [
        'type' => 'object',
        'properties' => [
          $recordKey => $item,
        ],
      ];
    }

    $itemsSchema = JsonSchema::fromArray([
      'type' => 'object',
      'properties' => [
        'financePlanItemAbc' => [
          'type' => 'object',
          'properties' => [
            'records' => $records,
          ],
          'required' => ['records'],
        ],
      ],
    ]);

    return new JsonFormsForm(JsonSchema::fromArray([
      'type' => 'object',
      'properties' => [$itemType => $itemsSchema],
      'required' => [$itemType],
    ]), new JsonFormsElement('test'));
  }

}
