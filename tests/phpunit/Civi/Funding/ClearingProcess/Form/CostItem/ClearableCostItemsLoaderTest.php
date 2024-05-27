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

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItem;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItems;
use Civi\Funding\ClearingProcess\Form\Container\ClearableItems;
use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\RemoteTools\JsonSchema\JsonSchemaArray;
use Civi\RemoteTools\JsonSchema\JsonSchemaNumber;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use ColinODell\PsrTestLogger\TestLogger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Form\CostItem\ClearableCostItemsLoader
 * @covers \Civi\Funding\ClearingProcess\Form\AbstractClearableItemsLoader
 */
final class ClearableCostItemsLoaderTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $itemManagerMock;

  /**
   * @var \Civi\Funding\ClearingProcess\Form\CostItem\ClearableCostItemsLoader
   */
  private ClearableCostItemsLoader $loader;

  private TestLogger $logger;

  protected function setUp(): void {
    parent::setUp();
    $this->itemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->logger = new TestLogger();
    $this->loader = new ClearableCostItemsLoader(
      $this->itemManagerMock,
      $this->logger
    );
  }

  public function testNumberItem(): void {
    $item = ApplicationCostItemFactory::createApplicationCostItem([
      'data_pointer' => '/test',
    ]);

    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $costItemSchema = new JsonSchemaCostItem([
      'type' => $item->getType(),
      'identifier' => $item->getIdentifier(),
      'clearing' => ['itemLabel' => 'test'],
    ]);
    $propertySchema = new JsonSchemaNumber([
      '$costItem' => $costItemSchema,
    ]);
    $jsonSchema = new JsonSchemaObject([
      'test' => $propertySchema,
    ]);

    $this->itemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn([$item]);

    static::assertEquals(
      ['#/properties/test' => new ClearableItems('#/properties/test', $propertySchema, $costItemSchema, [$item])],
      $this->loader->getClearableItems($applicationProcessBundle, $jsonSchema)
    );
  }

  public function testArrayItems(): void {
    $item0 = ApplicationCostItemFactory::createApplicationCostItem([
      'data_pointer' => '/test/0',
      'identifier' => 'item0',
      'amount' => 10.0,
      'properties' => [
        'foo' => 'bar0',
        'theIdentifier' => 'item0',
        'theAmount' => 10.0,
      ],
    ]);
    $item1 = ApplicationCostItemFactory::createApplicationCostItem([
      'data_pointer' => '/test/1',
      'identifier' => 'item1',
      'amount' => 20.0,
      'properties' => [
        'foo' => 'bar1',
        'theIdentifier' => 'item1',
        'theAmount' => 20.0,
      ],
    ]);

    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $costItemsSchema = new JsonSchemaCostItems([
      'type' => $item0->getType(),
      'identifierProperty' => 'theIdentifier',
      'amountProperty' => 'theAmount',
      'clearing' => ['itemLabel' => 'test'],
    ]);
    $propertySchema = new JsonSchemaArray(new JsonSchemaObject([
      'theIdentifier' => new JsonSchemaString(),
      'theAmount' => new JsonSchemaNumber(),
      'foo' => new JsonSchemaString(),
    ]), ['$costItems' => $costItemsSchema]);
    $jsonSchema = new JsonSchemaObject([
      'test' => $propertySchema,
    ]);

    $this->itemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn([$item0, $item1]);

    static::assertEquals(
      [
        '#/properties/test' => new ClearableItems(
          '#/properties/test', $propertySchema, $costItemsSchema, [$item0, $item1]
        ),
      ],
      $this->loader->getClearableItems($applicationProcessBundle, $jsonSchema)
    );
  }

  public function testNonClearableNumberItem(): void {
    $item = ApplicationCostItemFactory::createApplicationCostItem([
      'data_pointer' => '/test',
    ]);

    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $costItemSchema = new JsonSchemaCostItem([
      'type' => $item->getType(),
      'identifier' => $item->getIdentifier(),
    ]);
    $propertySchema = new JsonSchemaNumber([
      '$costItem' => $costItemSchema,
    ]);
    $jsonSchema = new JsonSchemaObject([
      'test' => $propertySchema,
    ]);

    $this->itemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn([$item]);

    static::assertSame([], $this->loader->getClearableItems($applicationProcessBundle, $jsonSchema));
  }

  public function testNonClearableArrayItems(): void {
    $item0 = ApplicationCostItemFactory::createApplicationCostItem([
      'data_pointer' => '/test/0',
      'identifier' => 'item0',
      'amount' => 10.0,
      'properties' => [
        'foo' => 'bar0',
        'theIdentifier' => 'item0',
        'theAmount' => 10.0,
      ],
    ]);
    $item1 = ApplicationCostItemFactory::createApplicationCostItem([
      'data_pointer' => '/test/1',
      'identifier' => 'item1',
      'amount' => 20.0,
      'properties' => [
        'foo' => 'bar1',
        'theIdentifier' => 'item1',
        'theAmount' => 20.0,
      ],
    ]);

    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $costItemsSchema = new JsonSchemaCostItems([
      'type' => $item0->getType(),
      'identifierProperty' => 'theIdentifier',
      'amountProperty' => 'theAmount',
    ]);
    $propertySchema = new JsonSchemaArray(new JsonSchemaObject([
      'theIdentifier' => new JsonSchemaString(),
      'theAmount' => new JsonSchemaNumber(),
      'foo' => new JsonSchemaString(),
    ]), ['$costItems' => $costItemsSchema]);
    $jsonSchema = new JsonSchemaObject([
      'test' => $propertySchema,
    ]);

    $this->itemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn([$item0, $item1]);

    static::assertSame([], $this->loader->getClearableItems($applicationProcessBundle, $jsonSchema));
  }

  public function testNoPropertySchema(): void {
    $item = ApplicationCostItemFactory::createApplicationCostItem([
      'data_pointer' => '/test',
    ]);

    $jsonSchema = new JsonSchemaObject([
      'someProperty' => new JsonSchemaNumber(),
    ]);

    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();

    $this->itemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn([$item]);

    static::assertSame([], $this->loader->getClearableItems($applicationProcessBundle, $jsonSchema));

    static::assertTrue($this->logger->hasError(sprintf(
      'No property schema found for item at "/test" in JSON schema of funding case type "%s"',
      $applicationProcessBundle->getFundingCaseType()->getName()
    )));
  }

  public function testNoCostItemSchema(): void {
    $item = ApplicationCostItemFactory::createApplicationCostItem([
      'data_pointer' => '/test',
    ]);

    $jsonSchema = new JsonSchemaObject([
      'test' => new JsonSchemaNumber(),
    ]);

    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();

    $this->itemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcessBundle->getApplicationProcess()->getId())
      ->willReturn([$item]);

    static::assertSame([], $this->loader->getClearableItems($applicationProcessBundle, $jsonSchema));

    static::assertTrue($this->logger->hasError(sprintf(
      'No finance plan item schema found for item at "/test" in JSON schema of funding case type "%s"',
      $applicationProcessBundle->getFundingCaseType()->getName()
    )));
  }

}
