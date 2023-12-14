<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\JsonSchema\CostItem;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidator;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemDataCollector
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemKeywordValidator
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemKeywordValidatorParser
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\NumberCostItemDataFactory
 */
final class CostItemKeywordTest extends TestCase {

  private CostItemDataCollector $costItemDataCollector;

  private OpisApplicationValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->costItemDataCollector = new CostItemDataCollector();
    $this->validator = new OpisApplicationValidator();
  }

  public function testNumber(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaObject([
        'amount1' => new JsonSchemaNumber([
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'foo',
            'identifier' => 'bar',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]),
      ]),
    ]);

    $data = (object) [
      'costs' => (object) [
        'amount1' => 123.45,
      ],
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['costItemDataCollector' => $this->costItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertTrue($this->costItemDataCollector->hasIdentifier('bar'));
    static::assertFalse($this->costItemDataCollector->hasIdentifier('baz'));
    static::assertEquals(
      [
        'bar' => new CostItemData([
          'type' => 'foo',
          'identifier' => 'bar',
          'amount' => 123.45,
          'properties' => [],
          'clearing' => ['itemLabel' => 'label'],
          'dataPointer' => '/costs/amount1',
          'dataType' => 'number',
        ]),
      ],
      $this->costItemDataCollector->getCostItemsData()
    );
  }

  public function testInteger(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaObject([
        'amount1' => new JsonSchemaInteger([
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'foo',
            'identifier' => 'bar',
          ]),
        ]),
      ]),
    ]);

    $data = (object) [
      'costs' => (object) [
        'amount1' => 123,
      ],
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['costItemDataCollector' => $this->costItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertEquals(
      [
        'bar' => new CostItemData([
          'type' => 'foo',
          'identifier' => 'bar',
          'amount' => 123.0,
          'properties' => [],
          'clearing' => NULL,
          'dataPointer' => '/costs/amount1',
          'dataType' => 'integer',
        ]),
      ],
      $this->costItemDataCollector->getCostItemsData()
    );
  }

  public function testZero(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaObject([
        'amount1' => new JsonSchemaNumber([
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'foo',
            'identifier' => 'bar',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]),
      ]),
    ]);

    $data = (object) [
      'costs' => (object) [
        'amount1' => 0.0,
      ],
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['costItemDataCollector' => $this->costItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertEquals([], $this->costItemDataCollector->getCostItemsData());
  }

  public function testNull(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaObject([
        'amount1' => new JsonSchemaNumber([
          '$costItem' => new JsonSchemaCostItem([
            'type' => 'foo',
            'identifier' => 'bar',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ], TRUE),
      ]),
    ]);

    $data = (object) [
      'costs' => (object) [
        'amount1' => NULL,
      ],
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['costItemDataCollector' => $this->costItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertSame([], $this->costItemDataCollector->getCostItemsData());
  }

}
