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

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItems;
use Civi\RemoteTools\JsonForms\Control\JsonFormsArray;
use Civi\RemoteTools\JsonForms\Control\JsonFormsHidden;
use Civi\RemoteTools\JsonForms\JsonFormsControl;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaBoolean;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Form\ItemDetailsFormElementGenerator
 */
final class ItemDetailsFormElementGeneratorTest extends TestCase {

  /**
   * @var \Civi\Funding\ClearingProcess\Form\ItemDetailsFormElementGenerator
   */
  private ItemDetailsFormElementGenerator $formElementGenerator;

  protected function setUp(): void {
    parent::setUp();
    $this->formElementGenerator = new ItemDetailsFormElementGenerator();
  }

  public function testWithControls(): void {
    $financePlanItemSchema = new JsonSchemaCostItems([
      'type' => 'xyz',
      'identifierProperty' => 'theIdentifier',
      'amountProperty' => 'theAmount',
      'clearing' => ['itemLabel' => 'label'],
    ]);

    $applicationPropertySchema = new JsonSchemaArray(new JsonSchemaObject([
      'theIdentifier' => new JsonSchemaString(),
      'foo' => new JsonSchemaString([
        'oneOf' => JsonSchemaUtil::buildTitledOneOf(['test' => 'testTitle', 'abc' => 'abcTitle']),
      ]),
      'theAmount' => new JsonSchemaNumber(),
      'bar' => new JsonSchemaNumber(['title' => 'BarTitle']),
      'baz' => new JsonSchemaBoolean(['title' => 'BazTitle']),
    ]), ['$costItems' => $financePlanItemSchema]);

    $applicationFormElement = new JsonFormsArray('#/costItem', 'Test', NULL, [
      // Should be not part of result header.
      new JsonFormsHidden('#/properties/theIdentifier'),
      // 'BarLabel' should be part of result header.
      new JsonFormsControl('#/properties/bar', 'BarLabel'),
      // 'BazTitle' should be part of result header.
      new JsonFormsControl('#/properties/baz', NULL),
      // 'Foo' should be part of result header. (Property name with first char upper-cased.)
      new JsonFormsControl('#/properties/foo', NULL),
    ]);

    $properties = [
      'theIdentifier' => 'identifier',
      'theAmount' => 1.23,
      'baz' => FALSE,
      'foo' => 'test',
      'bar' => 2,
    ];

    $table = $this->formElementGenerator->generateDetailsElement(
      $applicationPropertySchema,
      $financePlanItemSchema,
      $applicationFormElement,
      $properties
    );

    $expected = [
      'type' => 'Table',
      'header' => ['BarLabel', 'BazTitle', 'Foo'],
      'elements' => [
        [
          'type' => 'TableRow',
          'elements' => [
            [
              'type' => 'Markup',
              'content' => '2',
              'contentMediaType' => 'text/html',
            ],
            [
              'type' => 'Markup',
              'content' => 'No',
              'contentMediaType' => 'text/html',
            ],
            [
              'type' => 'Markup',
              // title from 'oneOf'
              'content' => 'testTitle',
              'contentMediaType' => 'text/html',
            ],
          ],
        ],
      ],
    ];

    static::assertEquals($expected, $table->toArray());
  }

  public function testWithoutControls(): void {
    $financePlanItemSchema = new JsonSchemaCostItems([
      'type' => 'xyz',
      'identifierProperty' => 'theIdentifier',
      'amountProperty' => 'theAmount',
      'clearing' => ['itemLabel' => 'label'],
    ]);

    $applicationPropertySchema = new JsonSchemaArray(new JsonSchemaObject([
      'theIdentifier' => new JsonSchemaString(),
      'foo' => new JsonSchemaString(),
      'theAmount' => new JsonSchemaNumber(),
      'bar' => new JsonSchemaNumber(['title' => 'BarTitle']),
      'baz' => new JsonSchemaBoolean(['title' => 'BazTitle']),
    ]), ['$costItems' => $financePlanItemSchema]);

    $applicationFormElement = new JsonFormsArray('#/costItem', 'Test');

    $properties = [
      'theIdentifier' => 'identifier',
      'theAmount' => 1.23,
      'baz' => FALSE,
      'foo' => 'test',
      'bar' => 2,
    ];

    $table = $this->formElementGenerator->generateDetailsElement(
      $applicationPropertySchema,
      $financePlanItemSchema,
      $applicationFormElement,
      $properties
    );

    $expected = [
      'type' => 'Table',
      'header' => ['TheIdentifier', 'Foo', 'BarTitle', 'BazTitle'],
      'elements' => [
        [
          'type' => 'TableRow',
          'elements' => [
            [
              'type' => 'Markup',
              'content' => 'identifier',
              'contentMediaType' => 'text/html',
            ],
            [
              'type' => 'Markup',
              'content' => 'test',
              'contentMediaType' => 'text/html',
            ],
            [
              'type' => 'Markup',
              'content' => '2',
              'contentMediaType' => 'text/html',
            ],
            [
              'type' => 'Markup',
              'content' => 'No',
              'contentMediaType' => 'text/html',
            ],
          ],
        ],
      ],
    ];

    static::assertEquals($expected, $table->toArray());
  }

}
