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

namespace Civi\Funding\Form\Application;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Form\Application\ApplicationCostItemsFormDataLoader
 */
final class ApplicationCostItemsFormDataLoaderTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $costItemManagerMock;

  private ApplicationCostItemsFormDataLoader $costItemsFormDataLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->costItemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->costItemsFormDataLoader = new ApplicationCostItemsFormDataLoader(
      $this->costItemManagerMock
    );
  }

  public function testAmountOnly(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $costItem = ApplicationCostItemEntity::fromArray([
      'application_process_id' => $applicationProcess->getId(),
      'identifier' => 'foo',
      'type' => 'bar',
      'amount' => 1.1,
      'properties' => [],
      'data_pointer' => '/foo/x/y',
    ]);
    $this->costItemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcess->getId())
      ->willReturn([$costItem]);

    $formData = ['foo' => ['bar' => 'baz']];
    $this->costItemsFormDataLoader->addCostItemsFormData($applicationProcess, $formData);
    static::assertEquals(
      [
        'foo' => [
          'bar' => 'baz',
          'x' => ['y' => 1.1],
        ],
      ],
      $formData
    );
  }

  public function testProperties(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $costItem = ApplicationCostItemEntity::fromArray([
      'application_process_id' => $applicationProcess->getId(),
      'identifier' => 'foo',
      'type' => 'bar',
      'amount' => 1.1,
      'properties' => ['y' => 'z'],
      'data_pointer' => '/foo/x',
    ]);
    $this->costItemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcess->getId())
      ->willReturn([$costItem]);

    $formData = ['foo' => ['bar' => 'baz']];
    $this->costItemsFormDataLoader->addCostItemsFormData($applicationProcess, $formData);
    static::assertEquals(
      [
        'foo' => [
          'bar' => 'baz',
          'x' => ['y' => 'z'],
        ],
      ],
      $formData
    );
  }

}
