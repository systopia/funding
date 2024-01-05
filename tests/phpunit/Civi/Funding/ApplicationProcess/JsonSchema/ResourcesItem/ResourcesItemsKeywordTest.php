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

namespace Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem;

use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidator;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ArrayResourcesItemDataFactory
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemDataCollector
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemsKeywordValidator
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemsKeywordValidatorParser
 */
final class ResourcesItemsKeywordTest extends TestCase {

  private ResourcesItemDataCollector $resourcesItemDataCollector;

  private OpisApplicationValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemDataCollector = new ResourcesItemDataCollector();
    $this->validator = new OpisApplicationValidator();
  }

  public function testNumber(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber(),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$resourcesItems' => new JsonSchemaResourcesItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'resources' => [
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
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertEquals(
      [
        'abc' => new ResourcesItemData([
          'type' => 'xyz',
          'identifier' => 'abc',
          'amount' => 123.45,
          'properties' => [
            'theIdentifier' => 'abc',
            'theAmount' => 123.45,
            'foo' => 'bar',
          ],
          'clearing' => ['itemLabel' => 'label'],
          'dataPointer' => '/resources/0',
          'dataType' => 'object',
        ]),
      ],
      $this->resourcesItemDataCollector->getResourcesItemsData()
    );
  }

  public function testInteger(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaInteger(),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$resourcesItems' => new JsonSchemaResourcesItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'resources' => [
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
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertEquals(
      [
        'abc' => new ResourcesItemData([
          'type' => 'xyz',
          'identifier' => 'abc',
          'amount' => 123,
          'properties' => [
            'theIdentifier' => 'abc',
            'theAmount' => 123,
            'foo' => 'bar',
          ],
          'clearing' => ['itemLabel' => 'label'],
          'dataPointer' => '/resources/0',
          'dataType' => 'object',
        ]),
      ],
      $this->resourcesItemDataCollector->getResourcesItemsData()
    );
  }

  public function testGeneratedIdentifier(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber(),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$resourcesItems' => new JsonSchemaResourcesItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'resources' => [
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
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    $resourcesItemsData = $this->resourcesItemDataCollector->getResourcesItemsData();
    static::assertCount(1, $resourcesItemsData);
    /** @var \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemData $resourcesItemData */
    $resourcesItemData = reset($resourcesItemsData);
    static::assertNotEmpty($resourcesItemData->getIdentifier());
    static::assertEquals(
      [
        $resourcesItemData->getIdentifier() => new ResourcesItemData([
          'type' => 'xyz',
          'identifier' => $resourcesItemData->getIdentifier(),
          'amount' => 123.45,
          'properties' => [
            'theIdentifier' => $resourcesItemData->getIdentifier(),
            'theAmount' => 123.45,
            'foo' => 'bar',
          ],
          'clearing' => ['itemLabel' => 'label'],
          'dataPointer' => '/resources/0',
          'dataType' => 'object',
        ]),
      ],
      $resourcesItemsData
    );

    // Generated identifier is set in $data.
    static::assertSame($resourcesItemData->getIdentifier(), $data->resources[0]->theIdentifier);
  }

  public function testNull(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchema([
        'type' => ['array', 'null'],
        'items' => new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber(),
          'foo' => new JsonSchemaString(),
        ]),
        '$resourcesItems' => new JsonSchemaResourcesItems([
          'type' => 'xyz',
          'identifierProperty' => 'theIdentifier',
          'amountProperty' => 'theAmount',
          'clearing' => ['itemLabel' => 'label'],
        ]),
      ]),
    ]);

    $data = (object) [
      'resources' => NULL,
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertSame([], $this->resourcesItemDataCollector->getResourcesItemsData());
  }

  public function testAmountNotSet(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber([]),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$resourcesItems' => new JsonSchemaResourcesItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'resources' => [
        (object) [
          'theIdentifier' => 'abc',
          'foo' => 'bar',
        ],
      ],
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertSame([], $this->resourcesItemDataCollector->getResourcesItemsData());
  }

  public function testAmountNull(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber([], TRUE),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$resourcesItems' => new JsonSchemaResourcesItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'resources' => [
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
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertSame([], $this->resourcesItemDataCollector->getResourcesItemsData());
  }

  public function testAmountZero(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaArray(
        new JsonSchemaObject([
          'theIdentifier' => new JsonSchemaString(),
          'theAmount' => new JsonSchemaNumber([], FALSE),
          'foo' => new JsonSchemaString(),
        ]),
        [
          '$resourcesItems' => new JsonSchemaResourcesItems([
            'type' => 'xyz',
            'identifierProperty' => 'theIdentifier',
            'amountProperty' => 'theAmount',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]
      ),
    ]);

    $data = (object) [
      'resources' => [
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
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertSame([], $this->resourcesItemDataCollector->getResourcesItemsData());
  }

}
