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
use Civi\RemoteTools\JsonSchema\JsonSchemaInteger;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemDataCollector
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemKeywordValidator
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemKeywordValidatorParser
 * @covers \Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\NumberResourcesItemDataFactory
 */
final class ResourcesItemKeywordTest extends TestCase {

  private ResourcesItemDataCollector $resourcesItemDataCollector;

  private OpisApplicationValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemDataCollector = new ResourcesItemDataCollector();
    $this->validator = new OpisApplicationValidator();
  }

  public function testNumber(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaObject([
        'amount1' => new JsonSchemaNumber([
          '$resourcesItem' => new JsonSchemaResourcesItem([
            'type' => 'foo',
            'identifier' => 'bar',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]),
      ]),
    ]);

    $data = (object) [
      'resources' => (object) [
        'amount1' => 123.45,
      ],
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertTrue($this->resourcesItemDataCollector->hasIdentifier('bar'));
    static::assertFalse($this->resourcesItemDataCollector->hasIdentifier('baz'));
    static::assertEquals(
      [
        'bar' => new ResourcesItemData([
          'type' => 'foo',
          'identifier' => 'bar',
          'amount' => 123.45,
          'properties' => [],
          'clearing' => ['itemLabel' => 'label'],
          'dataPointer' => '/resources/amount1',
          'dataType' => 'number',
        ]),
      ],
      $this->resourcesItemDataCollector->getResourcesItemsData()
    );
  }

  public function testInteger(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaObject([
        'amount1' => new JsonSchemaInteger([
          '$resourcesItem' => new JsonSchemaResourcesItem([
            'type' => 'foo',
            'identifier' => 'bar',
          ]),
        ]),
      ]),
    ]);

    $data = (object) [
      'resources' => (object) [
        'amount1' => 123,
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
        'bar' => new ResourcesItemData([
          'type' => 'foo',
          'identifier' => 'bar',
          'amount' => 123.0,
          'properties' => [],
          'clearing' => NULL,
          'dataPointer' => '/resources/amount1',
          'dataType' => 'integer',
        ]),
      ],
      $this->resourcesItemDataCollector->getResourcesItemsData()
    );
  }

  public function testZero(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaObject([
        'amount1' => new JsonSchemaNumber([
          '$resourcesItem' => new JsonSchemaResourcesItem([
            'type' => 'foo',
            'identifier' => 'bar',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ]),
      ]),
    ]);

    $data = (object) [
      'resources' => (object) [
        'amount1' => 0.0,
      ],
    ];

    $result = $this->validator->validate(
      $data,
      $jsonSchema->toStdClass(),
      ['resourcesItemDataCollector' => $this->resourcesItemDataCollector],
    );

    static::assertTrue($result->isValid());
    static::assertEquals([], $this->resourcesItemDataCollector->getResourcesItemsData());
  }

  public function testNull(): void {
    $jsonSchema = new JsonSchemaObject([
      'resources' => new JsonSchemaObject([
        'amount1' => new JsonSchemaNumber([
          '$resourcesItem' => new JsonSchemaResourcesItem([
            'type' => 'foo',
            'identifier' => 'bar',
            'clearing' => ['itemLabel' => 'label'],
          ]),
        ], TRUE),
      ]),
    ]);

    $data = (object) [
      'resources' => (object) [
        'amount1' => NULL,
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
