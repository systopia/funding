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

namespace Civi\Funding\ApplicationProcess\Clearing\CostItem;

use Civi\Core\Format;
use Civi\Funding\ApplicationProcess\Clearing\ClearingGroupExtractor;
use Civi\Funding\ApplicationProcess\Clearing\Container\ClearableItems;
use Civi\Funding\ApplicationProcess\Clearing\Container\ClearingItemsGroup;
use Civi\Funding\ApplicationProcess\Clearing\ItemDetailsFormElementGenerator;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItems;
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
 * @covers \Civi\Funding\ApplicationProcess\Clearing\CostItem\ClearingCostItemsJsonFormsGenerator
 * @covers \Civi\Funding\ApplicationProcess\Clearing\AbstractClearingItemsJsonFormsGenerator
 */
final class ClearingCostItemsJsonFormsGeneratorTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\Clearing\CostItem\ClearableCostItemsLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearableItemsLoaderMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\Clearing\ClearingGroupExtractor&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearingGroupExtractorMock;

  /**
   * @var \Civi\Core\Format&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $formatMock;

  private ClearingCostItemsJsonFormsGenerator $generator;

  /**
   * @var \Civi\Funding\ApplicationProcess\Clearing\ItemDetailsFormElementGenerator&\PHPUnit\Framework\MockObject\MockObject
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

    $this->itemDetailsFormElementGeneratorMock->expects(static::never())->method('generateDetailsElement');

    $this->formatMock->expects(static::once())->method('money')
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
                'amountRecordedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(honorare, "value.amount")), 2)',
                    'variables' => ['honorare' => ['$data' => '1/records']],
                  ],
                ],
                'amountAdmittedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(honorare, "value.amountAdmitted")), 2)',
                    'variables' => ['honorare' => ['$data' => '1/records']],
                  ],
                ],
                'records' => [
                  'type' => 'array',
                  'items' => [
                    'type' => 'object',
                    'properties' => [
                      'file' => [
                        'type' => 'string',
                        'format' => 'uri',
                      ],
                      'description' => [
                        'type' => 'string',
                      ],
                      'amount' => [
                        'type' => 'number',
                        'precision' => 2,
                      ],
                      'amountAdmitted' => [
                        'type' => 'number',
                        'precision' => 2,
                        'readOnly' => TRUE,
                      ],
                    ],
                    'required' => ['description', 'amount'],
                  ],
                ],
              ],
              'required' => ['records'],
            ],
          ],
          'required' => ['23'],
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
                'Amount Admitted in EUR',
                'Amount Recorded in EUR',
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
                      'scope' => '#/properties/costItems/properties/23/properties/amountAdmittedTotal',
                      'label' => '',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItems/properties/23/properties/amountRecordedTotal',
                      'label' => '',
                    ],
                  ],
                ],
              ],
            ],
            [
              'type' => 'Group',
              'label' => 'Proofs',
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
                          'scope' => '#/properties/file',
                          'label' => 'Proof',
                          'options' => ['format' => 'file'],
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/description',
                          'label' => 'Description',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amountAdmitted',
                          'label' => 'Amount Admitted in EUR',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amount',
                          'label' => 'Amount in EUR',
                        ],
                      ],
                    ],
                    'addButtonLabel' => 'Add Proof',
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

    $this->formatMock->expects(static::once())->method('money')
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
                'amountRecordedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(honorare, "value.amount")), 2)',
                    'variables' => ['honorare' => ['$data' => '1/records']],
                  ],
                ],
                'amountAdmittedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(honorare, "value.amountAdmitted")), 2)',
                    'variables' => ['honorare' => ['$data' => '1/records']],
                  ],
                ],
                'records' => [
                  'type' => 'array',
                  'items' => [
                    'type' => 'object',
                    'properties' => [
                      'file' => [
                        'type' => 'string',
                        'format' => 'uri',
                      ],
                      'description' => [
                        'type' => 'string',
                      ],
                      'amount' => [
                        'type' => 'number',
                        'precision' => 2,
                      ],
                      'amountAdmitted' => [
                        'type' => 'number',
                        'precision' => 2,
                        'readOnly' => TRUE,
                      ],
                    ],
                    'required' => ['description', 'amount'],
                  ],
                ],
              ],
              'required' => ['records'],
            ],
          ],
          'required' => ['23'],
        ],
      ],
      'required' => ['costItems'],
    ], $clearingItemsForm->getJsonSchema()->toArray());

    static::assertEquals([
      'type' => 'Group',
      'label' => 'Costs',
      'elements' => [
        [
          'type' => 'Table',
          'header' => [
            'Item',
            'Amount Approved',
            'Amount Admitted in EUR',
            'Amount Recorded in EUR',
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
                  'scope' => '#/properties/costItems/properties/23/properties/amountAdmittedTotal',
                  'label' => '',
                ],
                [
                  'type' => 'Control',
                  'scope' => '#/properties/costItems/properties/23/properties/amountRecordedTotal',
                  'label' => '',
                ],
              ],
            ],
          ],
        ],
        [
          'type' => 'Group',
          'label' => 'Proofs',
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
                      'scope' => '#/properties/file',
                      'label' => 'Proof',
                      'options' => ['format' => 'file'],
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/description',
                      'label' => 'Description',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/amountAdmitted',
                      'label' => 'Amount Admitted in EUR',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/amount',
                      'label' => 'Amount in EUR',
                    ],
                  ],
                ],
                'addButtonLabel' => 'Add Proof',
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

    $this->formatMock->expects(static::once())->method('money')
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
                'amountRecordedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(honorare, "value.amount")), 2)',
                    'variables' => ['honorare' => ['$data' => '1/records']],
                  ],
                ],
                'amountAdmittedTotal' => [
                  'type' => 'number',
                  '$calculate' => [
                    'expression' => 'round(sum(map(honorare, "value.amountAdmitted")), 2)',
                    'variables' => ['honorare' => ['$data' => '1/records']],
                  ],
                ],
                'records' => [
                  'type' => 'array',
                  'items' => [
                    'type' => 'object',
                    'properties' => [
                      'file' => [
                        'type' => 'string',
                        'format' => 'uri',
                      ],
                      'description' => [
                        'type' => 'string',
                      ],
                      'amount' => [
                        'type' => 'number',
                        'precision' => 2,
                      ],
                      'amountAdmitted' => [
                        'type' => 'number',
                        'precision' => 2,
                        'readOnly' => TRUE,
                      ],
                    ],
                    'required' => ['description', 'amount'],
                  ],
                ],
              ],
              'required' => ['records'],
            ],
          ],
          'required' => ['23'],
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
                'Amount Admitted in EUR',
                'Amount Recorded in EUR',
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
                      'scope' => '#/properties/costItems/properties/23/properties/amountAdmittedTotal',
                      'label' => '',
                    ],
                    [
                      'type' => 'Control',
                      'scope' => '#/properties/costItems/properties/23/properties/amountRecordedTotal',
                      'label' => '',
                    ],
                  ],
                ],
              ],
            ],
            $itemDetailsFormElement->toArray(),
            [
              'type' => 'Group',
              'label' => 'Proofs',
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
                          'scope' => '#/properties/file',
                          'label' => 'Proof',
                          'options' => ['format' => 'file'],
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/description',
                          'label' => 'Description',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amountAdmitted',
                          'label' => 'Amount Admitted in EUR',
                        ],
                        [
                          'type' => 'Control',
                          'scope' => '#/properties/amount',
                          'label' => 'Amount in EUR',
                        ],
                      ],
                    ],
                    'addButtonLabel' => 'Add Proof',
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
