<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Translation;

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItems;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\JsonSchemaResourcesItem;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\JsonSchemaResourcesItems;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaMoney;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Translation\JsonSchemaStringTranslator
 */
final class JsonSchemaStringTranslatorTest extends TestCase {

  public function testTranslateStringsOneOf(): void {
    $translator = new JsonSchemaStringTranslator();

    // Translate title of oneOf item.
    $schema = new JsonSchemaString([
      'oneOf' => JsonSchema::convertToJsonSchemaArray([
        ['const' => 'foo', 'title' => 'Foo'],
      ]),
    ]);
    $translator->translateStrings($schema, ['Foo' => 'Foo2'], 'en_US');
    static::assertSame('Foo2', $schema['oneOf'][0]['title'] ?? NULL);
  }

  public function testTranslateStringValidationMessage(): void {
    $translator = new JsonSchemaStringTranslator();

    // Translate validation message in object property.
    $stringSchema = new JsonSchemaString([
      '$validations' => JsonSchema::convertToJsonSchemaArray([
        [
          'keyword' => 'const',
          'value' => 'Test',
          'message' => 'Message',
        ],
      ]),
    ]);
    $schema = new JsonSchemaObject([
      'string' => $stringSchema,
    ]);
    $translator->translateStrings($schema, ['Message' => 'Message2'], 'en_US');
    static::assertSame('Message2', $stringSchema['$validations'][0]['message'] ?? NULL);

    // Translate validation message in array items schema.
    $stringSchema = new JsonSchemaString([
      '$validations' => JsonSchema::convertToJsonSchemaArray([
        [
          'keyword' => 'const',
          'value' => 'Test',
          'message' => 'Message',
        ],
      ]),
    ]);
    $schema = new JsonSchemaArray($stringSchema);
    $translator->translateStrings($schema, ['Message' => 'Message2'], 'en_US');
    static::assertSame('Message2', $stringSchema['$validations'][0]['message'] ?? NULL);
  }

  public function testTranslateStringsCostItem(): void {
    $translator = new JsonSchemaStringTranslator();

    // Translate labels in cost item schema.
    $costItemSchema = new JsonSchemaCostItem([
      'type' => 'foo',
      'identifier' => 'bar',
      'clearing' => [
        'itemLabel' => 'Item',
        'recipientLabel' => 'Recipient',
      ],
    ]);
    $schema = new JsonSchemaMoney([
      '$costItem' => $costItemSchema,
    ]);
    $translator->translateStrings($schema, ['Item' => 'Item2', 'Recipient' => 'Recipient2'], 'en_US');
    static::assertSame('Item2', $costItemSchema['clearing']['itemLabel'] ?? NULL);
    static::assertSame('Recipient2', $costItemSchema['clearing']['recipientLabel'] ?? NULL);
  }

  public function testTranslateStringsCostItems(): void {
    $translator = new JsonSchemaStringTranslator();

    // Translate labels in cost items schema.
    $costItemsSchema = new JsonSchemaCostItems([
      'type' => 'foo',
      'identifierProperty' => 'identifier',
      'amountProperty' => 'amount',
      'clearing' => [
        'itemLabel' => 'Item',
        'recipientLabel' => 'Recipient',
      ],
    ]);
    $schema = new JsonSchemaArray(
      new JsonSchemaObject([]), [
        '$costItems' => $costItemsSchema,
      ]
    );
    $translator->translateStrings($schema, ['Item' => 'Item2', 'Recipient' => 'Recipient2'], 'en_US');
    static::assertSame('Item2', $costItemsSchema['clearing']['itemLabel'] ?? NULL);
    static::assertSame('Recipient2', $costItemsSchema['clearing']['recipientLabel'] ?? NULL);
  }

  public function testTranslateStringsResourcesItem(): void {
    $translator = new JsonSchemaStringTranslator();

    // Translate labels in resources item schema.
    $resourcesItemSchema = new JsonSchemaResourcesItem([
      'type' => 'foo',
      'identifier' => 'bar',
      'clearing' => [
        'itemLabel' => 'Item',
        'recipientLabel' => 'Recipient',
      ],
    ]);
    $schema = new JsonSchemaMoney([
      '$resourcesItem' => $resourcesItemSchema,
    ]);
    $translator->translateStrings($schema, ['Item' => 'Item2', 'Recipient' => 'Recipient2'], 'en_US');
    static::assertSame('Item2', $resourcesItemSchema['clearing']['itemLabel'] ?? NULL);
    static::assertSame('Recipient2', $resourcesItemSchema['clearing']['recipientLabel'] ?? NULL);
  }

  public function testTranslateStringsResourcesItems(): void {
    $translator = new JsonSchemaStringTranslator();

    // Translate labels in resources items schema.
    $resourcesItemsSchema = new JsonSchemaResourcesItems([
      'type' => 'foo',
      'identifierProperty' => 'identifier',
      'amountProperty' => 'amount',
      'clearing' => [
        'itemLabel' => 'Item',
        'recipientLabel' => 'Recipient',
      ],
    ]);
    $schema = new JsonSchemaArray(
      new JsonSchemaObject([]),
      [
        '$resourcesItems' => $resourcesItemsSchema,
      ]
    );
    $translator->translateStrings($schema, ['Item' => 'Item2', 'Recipient' => 'Recipient2'], 'en_US');
    static::assertSame('Item2', $resourcesItemsSchema['clearing']['itemLabel'] ?? NULL);
    static::assertSame('Recipient2', $resourcesItemsSchema['clearing']['recipientLabel'] ?? NULL);
  }

}
