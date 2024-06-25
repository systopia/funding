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

namespace Civi\Funding\ClearingProcess\Form\CostItem;

use Civi\Core\Format;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItems;
use Civi\Funding\ClearingProcess\Form\ClearingGroupExtractor;
use Civi\Funding\ClearingProcess\Form\Container\ClearableItems;
use Civi\Funding\ClearingProcess\Form\Container\ClearingItemsGroup;
use Civi\Funding\ClearingProcess\Form\ItemDetailsFormElementGenerator;
use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Form\JsonFormsForm;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsTable;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Form\CostItem\ClearingCostItemsJsonFormsGenerator
 * @covers \Civi\Funding\ClearingProcess\Form\AbstractClearingItemsJsonFormsGenerator
 */
final class ClearingCostItemsJsonFormsGeneratorTest extends TestCase {

  /**
   * @var \Civi\Funding\ClearingProcess\Form\CostItem\ClearableCostItemsLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearableItemsLoaderMock;

  /**
   * @var \Civi\Funding\ClearingProcess\Form\ClearingGroupExtractor&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearingGroupExtractorMock;

  /**
   * @var \Civi\Core\Format&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formatMock;

  private ClearingCostItemsJsonFormsGenerator $generator;

  /**
   * @var \Civi\Funding\ClearingProcess\Form\ItemDetailsFormElementGenerator&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $itemDetailsFormElementGeneratorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->clearableItemsLoaderMock = $this->createMock(ClearableCostItemsLoader::class);
    $this->clearingGroupExtractorMock = $this->createMock(ClearingGroupExtractor::class);
    $this->formatMock = $this->createMock(Format::class);
    $this->itemDetailsFormElementGeneratorMock = $this->createMock(ItemDetailsFormElementGenerator::class);

    $this->generator = new ClearingCostItemsJsonFormsGenerator(
      $this->clearableItemsLoaderMock,
      $this->clearingGroupExtractorMock,
      $this->formatMock,
      $this->itemDetailsFormElementGeneratorMock
    );
  }

  public function testNumberItem(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();

    $costItem = ApplicationCostItemFactory::createApplicationCostItem([
      'id' => 23,
      'data_pointer' => '/test',
      'identifier' => 'test-cost-item',
    ]);
    $costItemSchema = new JsonSchemaCostItem([
      'type' => $costItem->getType(),
      'identifier' => 'test-cost-item',
      'clearing' => ['itemLabel' => 'TestItemLabel'],
    ]);
    $propertySchema = new JsonSchemaNumber(['$costItem' => $costItemSchema]);
    $jsonSchema = new JsonSchemaObject(['test' => $propertySchema]);
    $uiSchema = new JsonFormsGroup('Test', []);
    $applicationForm = new JsonFormsForm($jsonSchema, $uiSchema);

    $this->clearableItemsLoaderMock->method('getClearableItems')
      ->with($applicationProcessBundle, $jsonSchema)
      ->willReturn([
        '#/properties/test' => new ClearableItems('#/properties', $propertySchema, $costItemSchema, [$costItem]),
      ]);

    $this->clearingGroupExtractorMock->method('extractGroups')
      ->with($uiSchema, ['#/properties/test'])
      ->willReturn([new ClearingItemsGroup('TestGroup', ['#/properties/test' => $propertySchema])]);

    $amountApprovedOverall = $costItem->getAmount();
    $formatSeries = [$amountApprovedOverall, $costItem->getAmount()];
    $this->formatMock->expects(static::exactly(2))->method('money')
      ->willReturnCallback(function (float $amount) use (&$formatSeries) {
        static::assertEquals(array_shift($formatSeries), $amount);

        return $amount . ' €';
      });

    $this->formatMock->expects(static::exactly(2))->method('money')
      ->with($costItem->getAmount(), 'EUR')
      ->willReturn($costItem->getAmount() . ' €');

    $clearingItemsForm = $this->generator->generate($applicationProcessBundle, $applicationForm);
    static::assertEquals([
      'type' => 'object',
      'properties' => [
        'costItems' => [
          'type' => 'object',
          'properties' => [
            '23' => [
              'type' => 'object',
              'properties' => [
                'records' => [
                  'type' => 'array',
                  'items' => [
                    'type' => 'object',
                    'properties' => [
                      '_id' => [
                        'type' => ['integer', 'null'],
                        'readOnly' => TRUE,
                        'default' => NULL,
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
                    'required' => ['paymentDate', 'recipient', 'reason', 'amount'],
                  ],
                ],
                'amountRecordedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(records, "value.amount")), 2)',
                    'variables' => ['records' => ['$data' => '1/records']],
                  ],
                  'default' => 0,
                ],
                'amountAdmittedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(records, "value.amountAdmitted ?: 0")), 2)',
                    'variables' => ['records' => ['$data' => '1/records']],
                  ],
                  'default' => 0,
                ],
              ],
              'required' => ['records'],
            ],
          ],
        ],
        'costItemsAmountRecorded' => [
          'type' => 'number',
          '$calculate' => [
            'expression' => 'round(item23Recorded, 2)',
            'variables' => [
              'item23Recorded' => [
                '$data' => '1/costItems/23/amountRecordedTotal',
                'fallback' => 0,
              ],
            ],
          ],
        ],
        'costItemsAmountAdmitted' => [
          'type' => 'number',
          '$calculate' => [
            'expression' => 'round(item23Admitted, 2)',
            'variables' => [
              'item23Admitted' => [
                '$data' => '1/costItems/23/amountAdmittedTotal',
                'fallback' => 0,
              ],
            ],
          ],
        ],
        'costItemsByType' => [
          'type' => 'object',
          'properties' => [
            'amountRecorded_test' => [
              'type' => 'number',
              '$calculate' => [
                'expression' => 'round(item23Recorded, 2)',
                'variables' => [
                  'item23Recorded' => [
                    '$data' => '2/costItems/23/amountRecordedTotal',
                    'fallback' => 0,
                  ],
                ],
              ],
            ],
            'amountAdmitted_test' => [
              'type' => 'number',
              '$calculate' => [
                'expression' => 'round(item23Admitted, 2)',
                'variables' => [
                  'item23Admitted' => [
                    '$data' => '2/costItems/23/amountAdmittedTotal',
                    'fallback' => 0,
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
      'required' => ['costItems'],
    ], $clearingItemsForm->getJsonSchema()->toArray());

    static::assertEquals([
      'type' => 'Group',
      'label' => 'Costs',
      'elements' => [
        [
          'type' => 'Group',
          'label' => 'TestGroup',
          'elements' => [
            [
              'type' => 'Table',
              'header' => [
                'Item',
                'Amount Approved',
                'Amount Recorded in EUR',
                'Amount Admitted in EUR',
              ],
              'elements' => [
                [
                  'type' => 'TableRow',
                  'elements' => [
                    [
                      'type' => 'Markup',
                      'contentMediaType' => 'text/html',
                      'content' => 'TestItemLabel',
                    ],
                    [
                      'type' => 'Markup',
                      'contentMediaType' => 'text/html',
                      'content' => $costItem->getAmount() . ' €',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItems/properties/23/properties/amountRecordedTotal',
                      'label' => '',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItems/properties/23/properties/amountAdmittedTotal',
                      'label' => '',
                    ],
                  ],
                ],
              ],
            ],
            [
              'type' => 'Group',
              'label' => 'Receipts',
              'options' => ['closeable' => TRUE],
              'elements' => [
                [
                  'type' => 'Control',
                  'scope' => '#/properties/costItems/properties/23/properties/records',
                  'label' => '',
                  'options' => [
                    'detail' => [
                      'elements' => [
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/_id',
                          'label' => '',
                          'options' => ['type' => 'hidden', 'internal' => TRUE],
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/file',
                          'label' => 'Receipt',
                          'options' => ['format' => 'file'],
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/receiptNumber',
                          'label' => 'Receipt Number',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/paymentDate',
                          'label' => "Payment/\u{200B}Posting Date",
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/recipient',
                          'label' => 'Payment Recipient',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/reason',
                          'label' => "Reason for Payment/\u{200B}Payment Reference",
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amount',
                          'label' => 'Amount in EUR',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amountAdmitted',
                          'label' => 'Amount Admitted in EUR',
                        ],
                      ],
                    ],
                    'addButtonLabel' => 'Add Receipt',
                  ],
                ],
              ],
            ],
          ],
        ],
        [
          'type' => 'Group',
          'label' => 'Overall',
          'elements' => [
            [
              'type' => 'Table',
              'header' => [
                'Amount Approved',
                'Amount Recorded in EUR',
                'Amount Admitted in EUR',
              ],
              'elements' => [
                [
                  'type' => 'TableRow',
                  'elements' => [
                    [
                      'type' => 'Markup',
                      'content' => $amountApprovedOverall . ' €',
                      'contentMediaType' => 'text/html',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItemsAmountRecorded',
                      'label' => '',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItemsAmountAdmitted',
                      'label' => '',
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ], $clearingItemsForm->getUiSchema()->toArray());
  }

  public function testNumberItemWithoutGroup(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();

    $costItem = ApplicationCostItemFactory::createApplicationCostItem([
      'id' => 23,
      'data_pointer' => '/test',
      'identifier' => 'test-cost-item',
    ]);
    $costItemSchema = new JsonSchemaCostItem([
      'type' => $costItem->getType(),
      'identifier' => 'test-cost-item',
      'clearing' => ['itemLabel' => 'TestItemLabel'],
    ]);
    $propertySchema = new JsonSchemaNumber(['$costItem' => $costItemSchema]);
    $jsonSchema = new JsonSchemaObject(['test' => $propertySchema]);
    $uiSchema = new JsonFormsGroup('Test', []);
    $applicationForm = new JsonFormsForm($jsonSchema, $uiSchema);

    $this->clearableItemsLoaderMock->method('getClearableItems')
      ->with($applicationProcessBundle, $jsonSchema)
      ->willReturn([
        '#/properties/test' => new ClearableItems('#/properties', $propertySchema, $costItemSchema, [$costItem]),
      ]);

    $this->clearingGroupExtractorMock->method('extractGroups')
      ->with($uiSchema, ['#/properties/test'])
      ->willReturn([new ClearingItemsGroup(NULL, ['#/properties/test' => $propertySchema])]);

    $this->itemDetailsFormElementGeneratorMock->expects(static::never())->method('generateDetailsElement');

    $amountApprovedOverall = $costItem->getAmount();
    $formatSeries = [$amountApprovedOverall, $costItem->getAmount()];
    $this->formatMock->expects(static::exactly(2))->method('money')
      ->willReturnCallback(function (float $amount) use (&$formatSeries) {
        static::assertEquals(array_shift($formatSeries), $amount);

        return $amount . ' €';
      });

    $clearingItemsForm = $this->generator->generate($applicationProcessBundle, $applicationForm);
    static::assertEquals([
      'type' => 'object',
      'properties' => [
        'costItems' => [
          'type' => 'object',
          'properties' => [
            '23' => [
              'type' => 'object',
              'properties' => [
                'records' => [
                  'type' => 'array',
                  'items' => [
                    'type' => 'object',
                    'properties' => [
                      '_id' => [
                        'type' => ['integer', 'null'],
                        'readOnly' => TRUE,
                        'default' => NULL,
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
                    'required' => ['paymentDate', 'recipient', 'reason', 'amount'],
                  ],
                ],
                'amountRecordedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(records, "value.amount")), 2)',
                    'variables' => ['records' => ['$data' => '1/records']],
                  ],
                  'default' => 0,
                ],
                'amountAdmittedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(records, "value.amountAdmitted ?: 0")), 2)',
                    'variables' => ['records' => ['$data' => '1/records']],
                  ],
                  'default' => 0,
                ],
              ],
              'required' => ['records'],
            ],
          ],
        ],
        'costItemsAmountRecorded' => [
          'type' => 'number',
          '$calculate' => [
            'expression' => 'round(item23Recorded, 2)',
            'variables' => [
              'item23Recorded' => [
                '$data' => '1/costItems/23/amountRecordedTotal',
                'fallback' => 0,
              ],
            ],
          ],
        ],
        'costItemsAmountAdmitted' => [
          'type' => 'number',
          '$calculate' => [
            'expression' => 'round(item23Admitted, 2)',
            'variables' => [
              'item23Admitted' => [
                '$data' => '1/costItems/23/amountAdmittedTotal',
                'fallback' => 0,
              ],
            ],
          ],
        ],
        'costItemsByType' => [
          'type' => 'object',
          'properties' => [
            'amountRecorded_test' => [
              'type' => 'number',
              '$calculate' => [
                'expression' => 'round(item23Recorded, 2)',
                'variables' => [
                  'item23Recorded' => [
                    '$data' => '2/costItems/23/amountRecordedTotal',
                    'fallback' => 0,
                  ],
                ],
              ],
            ],
            'amountAdmitted_test' => [
              'type' => 'number',
              '$calculate' => [
                'expression' => 'round(item23Admitted, 2)',
                'variables' => [
                  'item23Admitted' => [
                    '$data' => '2/costItems/23/amountAdmittedTotal',
                    'fallback' => 0,
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
      'required' => ['costItems'],
    ], $clearingItemsForm->getJsonSchema()->toArray());

    static::assertEquals([
      'type' => 'Group',
      'label' => 'Costs',
      'elements' => [
        [
          'type' => 'Group',
          'label' => 'TestItemLabel',
          'elements' => [
            [
              'type' => 'Table',
              'header' => [
                'Item',
                'Amount Approved',
                'Amount Recorded in EUR',
                'Amount Admitted in EUR',
              ],
              'elements' => [
                [
                  'type' => 'TableRow',
                  'elements' => [
                    [
                      'type' => 'Markup',
                      'contentMediaType' => 'text/html',
                      'content' => 'TestItemLabel',
                    ],
                    [
                      'type' => 'Markup',
                      'contentMediaType' => 'text/html',
                      'content' => $costItem->getAmount() . ' €',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItems/properties/23/properties/amountRecordedTotal',
                      'label' => '',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItems/properties/23/properties/amountAdmittedTotal',
                      'label' => '',
                    ],
                  ],
                ],
              ],
            ],
            [
              'type' => 'Group',
              'label' => 'Receipts',
              'options' => ['closeable' => TRUE],
              'elements' => [
                [
                  'type' => 'Control',
                  'scope' => '#/properties/costItems/properties/23/properties/records',
                  'label' => '',
                  'options' => [
                    'detail' => [
                      'elements' => [
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/_id',
                          'label' => '',
                          'options' => ['type' => 'hidden', 'internal' => TRUE],
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/file',
                          'label' => 'Receipt',
                          'options' => ['format' => 'file'],
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/receiptNumber',
                          'label' => 'Receipt Number',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/paymentDate',
                          'label' => "Payment/\u{200B}Posting Date",
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/recipient',
                          'label' => 'Payment Recipient',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/reason',
                          'label' => "Reason for Payment/\u{200B}Payment Reference",
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amount',
                          'label' => 'Amount in EUR',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amountAdmitted',
                          'label' => 'Amount Admitted in EUR',
                        ],
                      ],
                    ],
                    'addButtonLabel' => 'Add Receipt',
                  ],
                ],
              ],
            ],
          ],
        ],
        [
          'type' => 'Group',
          'label' => 'Overall',
          'elements' => [
            [
              'type' => 'Table',
              'header' => [
                'Amount Approved',
                'Amount Recorded in EUR',
                'Amount Admitted in EUR',
              ],
              'elements' => [
                [
                  'type' => 'TableRow',
                  'elements' => [
                    [
                      'type' => 'Markup',
                      'content' => $amountApprovedOverall . ' €',
                      'contentMediaType' => 'text/html',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItemsAmountRecorded',
                      'label' => '',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItemsAmountAdmitted',
                      'label' => '',
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ], $clearingItemsForm->getUiSchema()->toArray());
  }

  public function testArrayItem(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();

    $costItem = ApplicationCostItemFactory::createApplicationCostItem([
      'id' => 23,
      'data_pointer' => '/test/0',
      'identifier' => 'test-cost-item',
      'amount' => 1.23,
      'properties' => [
        'theIdentifier' => 'test-cost-item',
        'theAmount' => 1.23,
        'foo' => 'bar',
      ],
    ]);
    $costItemsSchema = new JsonSchemaCostItems([
      'type' => $costItem->getType(),
      'identifierProperty' => 'theIdentifier',
      'amountProperty' => 'theAmount',
      'clearing' => ['itemLabel' => 'TestItemLabel'],
    ]);
    $propertySchema = new JsonSchemaArray(new JsonSchemaObject([
      'theIdentifier' => new JsonSchemaString(),
      'theAmount' => new JsonSchemaNumber(),
      'foo' => new JsonSchemaString(),
    ]), ['$costItems' => $costItemsSchema]);
    $jsonSchema = new JsonSchemaObject(['test' => $propertySchema]);
    $uiSchema = new JsonFormsGroup('Test', []);
    $applicationForm = new JsonFormsForm($jsonSchema, $uiSchema);

    $this->clearableItemsLoaderMock->method('getClearableItems')
      ->with($applicationProcessBundle, $jsonSchema)
      ->willReturn([
        '#/properties/test' => new ClearableItems('#/properties', $propertySchema, $costItemsSchema, [$costItem]),
      ]);

    $this->clearingGroupExtractorMock->method('extractGroups')
      ->with($uiSchema, ['#/properties/test'])
      ->willReturn([new ClearingItemsGroup('TestGroup', ['#/properties/test' => $propertySchema])]);

    $itemDetailsFormElement = new JsonFormsTable(['abc'], []);
    $this->itemDetailsFormElementGeneratorMock->method('generateDetailsElement')
      ->willReturn($itemDetailsFormElement);

    $amountApprovedOverall = $costItem->getAmount();
    $formatSeries = [$amountApprovedOverall, $costItem->getAmount()];
    $this->formatMock->expects(static::exactly(2))->method('money')
      ->willReturnCallback(function (float $amount) use (&$formatSeries) {
        static::assertEquals(array_shift($formatSeries), $amount);

        return $amount . ' €';
      });

    $clearingItemsForm = $this->generator->generate($applicationProcessBundle, $applicationForm);
    static::assertEquals([
      'type' => 'object',
      'properties' => [
        'costItems' => [
          'type' => 'object',
          'properties' => [
            '23' => [
              'type' => 'object',
              'properties' => [
                'records' => [
                  'type' => 'array',
                  'items' => [
                    'type' => 'object',
                    'properties' => [
                      '_id' => [
                        'type' => ['integer', 'null'],
                        'readOnly' => TRUE,
                        'default' => NULL,
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
                    'required' => ['paymentDate', 'recipient', 'reason', 'amount'],
                  ],
                ],
                'amountRecordedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(records, "value.amount")), 2)',
                    'variables' => ['records' => ['$data' => '1/records']],
                  ],
                  'default' => 0,
                ],
                'amountAdmittedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(records, "value.amountAdmitted ?: 0")), 2)',
                    'variables' => ['records' => ['$data' => '1/records']],
                  ],
                  'default' => 0,
                ],
              ],
              'required' => ['records'],
            ],
          ],
        ],
        'costItemsAmountRecorded' => [
          'type' => 'number',
          '$calculate' => [
            'expression' => 'round(item23Recorded, 2)',
            'variables' => [
              'item23Recorded' => [
                '$data' => '1/costItems/23/amountRecordedTotal',
                'fallback' => 0,
              ],
            ],
          ],
        ],
        'costItemsAmountAdmitted' => [
          'type' => 'number',
          '$calculate' => [
            'expression' => 'round(item23Admitted, 2)',
            'variables' => [
              'item23Admitted' => [
                '$data' => '1/costItems/23/amountAdmittedTotal',
                'fallback' => 0,
              ],
            ],
          ],
        ],
        'costItemsByType' => [
          'type' => 'object',
          'properties' => [
            'amountRecorded_test' => [
              'type' => 'number',
              '$calculate' => [
                'expression' => 'round(item23Recorded, 2)',
                'variables' => [
                  'item23Recorded' => [
                    '$data' => '2/costItems/23/amountRecordedTotal',
                    'fallback' => 0,
                  ],
                ],
              ],
            ],
            'amountAdmitted_test' => [
              'type' => 'number',
              '$calculate' => [
                'expression' => 'round(item23Admitted, 2)',
                'variables' => [
                  'item23Admitted' => [
                    '$data' => '2/costItems/23/amountAdmittedTotal',
                    'fallback' => 0,
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
      'required' => ['costItems'],
    ], $clearingItemsForm->getJsonSchema()->toArray());

    static::assertEquals([
      'type' => 'Group',
      'label' => 'Costs',
      'elements' => [
        [
          'type' => 'Group',
          'label' => 'TestGroup',
          'elements' => [
            [
              'type' => 'Table',
              'header' => [
                'Item',
                'Amount Approved',
                'Amount Recorded in EUR',
                'Amount Admitted in EUR',
              ],
              'elements' => [
                [
                  'type' => 'TableRow',
                  'elements' => [
                    [
                      'type' => 'Markup',
                      'contentMediaType' => 'text/html',
                      'content' => 'TestItemLabel',
                    ],
                    [
                      'type' => 'Markup',
                      'contentMediaType' => 'text/html',
                      'content' => $costItem->getAmount() . ' €',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItems/properties/23/properties/amountRecordedTotal',
                      'label' => '',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItems/properties/23/properties/amountAdmittedTotal',
                      'label' => '',
                    ],
                  ],
                ],
              ],
            ],
            $itemDetailsFormElement->toArray(),
            [
              'type' => 'Group',
              'label' => 'Receipts',
              'options' => ['closeable' => TRUE],
              'elements' => [
                [
                  'type' => 'Control',
                  'scope' => '#/properties/costItems/properties/23/properties/records',
                  'label' => '',
                  'options' => [
                    'detail' => [
                      'elements' => [
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/_id',
                          'label' => '',
                          'options' => ['type' => 'hidden', 'internal' => TRUE],
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/file',
                          'label' => 'Receipt',
                          'options' => ['format' => 'file'],
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/receiptNumber',
                          'label' => 'Receipt Number',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/paymentDate',
                          'label' => "Payment/\u{200B}Posting Date",
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/recipient',
                          'label' => 'Payment Recipient',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/reason',
                          'label' => "Reason for Payment/\u{200B}Payment Reference",
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amount',
                          'label' => 'Amount in EUR',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amountAdmitted',
                          'label' => 'Amount Admitted in EUR',
                        ],
                      ],
                    ],
                    'addButtonLabel' => 'Add Receipt',
                  ],
                ],
              ],
            ],
          ],
        ],
        [
          'type' => 'Group',
          'label' => 'Overall',
          'elements' => [
            [
              'type' => 'Table',
              'header' => [
                'Amount Approved',
                'Amount Recorded in EUR',
                'Amount Admitted in EUR',
              ],
              'elements' => [
                [
                  'type' => 'TableRow',
                  'elements' => [
                    [
                      'type' => 'Markup',
                      'content' => $amountApprovedOverall . ' €',
                      'contentMediaType' => 'text/html',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItemsAmountRecorded',
                      'label' => '',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItemsAmountAdmitted',
                      'label' => '',
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
      ],
    ], $clearingItemsForm->getUiSchema()->toArray());
  }

}
