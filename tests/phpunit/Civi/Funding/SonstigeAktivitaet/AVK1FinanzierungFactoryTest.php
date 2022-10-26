<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\SonstigeAktivitaet;

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\AVK1FinanzierungFactory
 */
final class AVK1FinanzierungFactoryTest extends TestCase {

  private const DEFAULT_FINANZIERUNG = [
    'teilnehmerbeitraege' => 0.0,
    'eigenmittel' => 0.0,
    'oeffentlicheMittel' => [
      'europa' => 0.0,
      'bundeslaender' => 0.0,
      'staedteUndKreise' => 0.0,
    ],
    'sonstigeMittel' => [],
  ];

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $resourcesItemManagerMock;

  private AVK1FinanzierungFactory $finanzierungFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemManagerMock = $this->createMock(ApplicationResourcesItemManager::class);
    $this->finanzierungFactory = new AVK1FinanzierungFactory(
      $this->resourcesItemManagerMock
    );
  }

  public function testDefault(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $this->mockResourcesItemManager($applicationProcess, []);
    static::assertEquals(
      self::DEFAULT_FINANZIERUNG,
      $this->finanzierungFactory->createFinanzierung($applicationProcess)
    );
  }

  /**
   * @phpstan-param array<string, mixed> $finanzierungDiff
   *
   * @dataProvider provideResourcesItems
   */
  public function testCreateFinanzierung(ApplicationResourcesItemEntity $item, array $finanzierungDiff): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $this->mockResourcesItemManager($applicationProcess, [$item]);

    static::assertEquals(
      array_replace_recursive(self::DEFAULT_FINANZIERUNG, $finanzierungDiff),
      $this->finanzierungFactory->createFinanzierung($applicationProcess)
    );
  }

  /**
   * @phpstan-return iterable<array{ApplicationResourcesItemEntity, array<string, mixed>}>
   */
  public function provideResourcesItems(): iterable {
    yield [
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'teilnehmerbeitraege',
        'type' => 'teilnehmerbeitraege',
        'amount' => 123,
        'properties' => [],
      ]),
      ['teilnehmerbeitraege' => 123.0],
    ];

    yield [
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'eigenmittel',
        'type' => 'eigenmittel',
        'amount' => 123,
        'properties' => [],
      ]),
      ['eigenmittel' => 123.0],
    ];

    yield [
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'oeffentlicheMittel/europa',
        'type' => 'oeffentlicheMittel/europa',
        'amount' => 123,
        'properties' => [],
      ]),
      ['oeffentlicheMittel' => ['europa' => 123.0]],
    ];

    yield [
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'oeffentlicheMittel/bundeslaender',
        'type' => 'oeffentlicheMittel/bundeslaender',
        'amount' => 123,
        'properties' => [],
      ]),
      ['oeffentlicheMittel' => ['bundeslaender' => 123.0]],
    ];

    yield [
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'oeffentlicheMittel/staedteUndKreise',
        'type' => 'oeffentlicheMittel/staedteUndKreise',
        'amount' => 123,
        'properties' => [],
      ]),
      ['oeffentlicheMittel' => ['staedteUndKreise' => 123.0]],
    ];

    yield [
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'test',
        'type' => 'sonstigeMittel',
        'amount' => 123,
        'properties' => ['quelle' => 'abc'],
      ]),
      [
        'sonstigeMittel' => [
          [
            '_identifier' => 'test',
            'betrag' => 123.0,
            'quelle' => 'abc',
          ],
        ],
      ],
    ];
  }

  /**
   * @phpstan-param array<ApplicationResourcesItemEntity> $items
   */
  private function mockResourcesItemManager(ApplicationProcessEntity $applicationProcess, array $items): void {
    $this->resourcesItemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcess->getId())
      ->willReturn($items);
  }

}
