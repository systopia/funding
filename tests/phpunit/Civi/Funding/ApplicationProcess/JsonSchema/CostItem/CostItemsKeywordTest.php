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
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\ArrayCostItemDataFactory
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemDataCollector
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemsKeywordValidator
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemsKeywordValidatorParser
 */
final class CostItemsKeywordTest extends TestCase {

  private CostItemDataCollector $costItemDataCollector;

  private OpisApplicationValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->costItemDataCollector = new CostItemDataCollector();
    $this->validator = new OpisApplicationValidator();
  }

  public function testNumber(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber(),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'costs' => [
        (object) [
          'theIdentifier' => 'abc',
          'theAmount' => 123.45,
          'foo' => 'bar',
        ],
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
        'abc' => new CostItemData([
          'type' => 'xyz',
          'identifier' => 'abc',
          'amount' => 123.45,
          'properties' => [
            'theIdentifier' => 'abc',
            'theAmount' => 123.45,
            'foo' => 'bar',
          ],
          'clearing' => ['itemLabel' => 'label'],
          'dataPointer' => '/costs/0',
          'dataType' => 'object',
        ]),
      ],
      $this->costItemDataCollector->getCostItemsData()
    );
  }

  public function testInteger(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaInteger(),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'costs' => [
        (object) [
          'theIdentifier' => 'abc',
          'theAmount' => 123,
          'foo' => 'bar',
        ],
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
        'abc' => new CostItemData([
          'type' => 'xyz',
          'identifier' => 'abc',
          'amount' => 123,
          'properties' => [
            'theIdentifier' => 'abc',
            'theAmount' => 123,
            'foo' => 'bar',
          ],
          'clearing' => ['itemLabel' => 'label'],
          'dataPointer' => '/costs/0',
          'dataType' => 'object',
        ]),
      ],
      $this->costItemDataCollector->getCostItemsData()
    );
  }

  public function testGeneratedIdentifier(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber(),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'costs' => [
        (object) [
          'theIdentifier' => '',
          'theAmount' => 123.45,
          'foo' => 'bar',
        ],
      ],
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['costItemDataCollector' => $this->costItemDataCollector],
    );

    static::assertTrue($result->isValid());
    $costItemsData = $this->costItemDataCollector->getCostItemsData();
    static::assertCount(1, $costItemsData);
    /** @var \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemData $costItemData */
    $costItemData = reset($costItemsData);
    static::assertNotEmpty($costItemData->getIdentifier());
    static::assertEquals(
      [
        $costItemData->getIdentifier() => new CostItemData([
          'type' => 'xyz',
          'identifier' => $costItemData->getIdentifier(),
          'amount' => 123.45,
          'properties' => [
            'theIdentifier' => $costItemData->getIdentifier(),
            'theAmount' => 123.45,
            'foo' => 'bar',
          ],
          'clearing' => ['itemLabel' => 'label'],
          'dataPointer' => '/costs/0',
          'dataType' => 'object',
        ]),
      ],
      $costItemsData
    );

    // Generated identifier is set in $data.
    static::assertSame($costItemData->getIdentifier(), $data->costs[0]->theIdentifier);
  }

  public function testNull(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchema([
        'type' => ['array', 'null'],
        'items' => new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber(),
          'foo' => new JsonSchemaString(),
        ]),
        '$costItems' => new JsonSchemaCostItems([
          'type' => 'xyz',
          'identifierProperty' => 'theIdentifier',
          'amountProperty' => 'theAmount',
          'clearing' => ['itemLabel' => 'label'],
        ]),
      ]),
    ]);

    $data = (object) [
      'costs' => NULL,
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['costItemDataCollector' => $this->costItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertSame([], $this->costItemDataCollector->getCostItemsData());
  }

  public function testAmountNotSet(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber([]),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'costs' => [
        (object) [
          'theIdentifier' => 'abc',
          'foo' => 'bar',
        ],
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

  public function testAmountNull(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber([], TRUE),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'costs' => [
        (object) [
          'theIdentifier' => 'abc',
          'theAmount' => NULL,
          'foo' => 'bar',
        ],
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

  public function testAmountZero(): void {
    $jsonSchema = new JsonSchemaObject([
      'costs' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber([], FALSE),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$costItems' => new JsonSchemaCostItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'costs' => [
        (object) [
          'theIdentifier' => 'abc',
          'theAmount' => 0.0,
          'foo' => 'bar',
        ],
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
