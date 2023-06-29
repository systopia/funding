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

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\AVK1KostenFactory
 */
final class AVK1KostenFactoryTest extends TestCase {

  private const DEFAULT_KOSTEN = [
    'unterkunftUndVerpflegung' => 0.0,
    'honorare' => [],
    'fahrtkosten' => [
      'intern' => 0.0,
      'anTeilnehmerErstattet' => 0.0,
    ],
    'sachkosten' => [
      'ausstattung' => [],
    ],
    'sonstigeAusgaben' => [],
    'versicherung' => [
      'teilnehmer' => 0.0,
    ],
  ];

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $costItemManagerMock;

  /**
   * @var \Civi\Funding\SonstigeAktivitaet\AVK1KostenFactory
   */
  private AVK1KostenFactory $kostenFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->costItemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->kostenFactory = new AVK1KostenFactory($this->costItemManagerMock);
  }

  public function testDefault(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $this->mockCostItemManager($applicationProcess, []);
    static::assertEquals(
      self::DEFAULT_KOSTEN,
      $this->kostenFactory->createKosten($applicationProcess)
    );
  }

  /**
   * @phpstan-param array<string, mixed> $kostenDiff
   *
   * @dataProvider provideCostItems
   */
  public function testCreateKosten(ApplicationCostItemEntity $item, array $kostenDiff): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess();
    $this->mockCostItemManager($applicationProcess, [$item]);

    static::assertEquals(
      array_replace_recursive(self::DEFAULT_KOSTEN, $kostenDiff),
      $this->kostenFactory->createKosten($applicationProcess)
    );
  }

  /**
   * @phpstan-return iterable<array{ApplicationCostItemEntity, array<string, mixed>}>
   */
  public function provideCostItems(): iterable {
    yield [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'unterkunftUndVerpflegung',
        'type' => 'unterkunftUndVerpflegung',
        'amount' => 123,
        'properties' => [],
      ]),
      ['unterkunftUndVerpflegung' => 123.0],
    ];

    yield [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'test',
        'type' => 'honorar',
        'amount' => 44,
        'properties' => [
          'stunden' => 2,
          'verguetung' => 22,
          'leistung' => 'foo',
          'qualifikation' => 'bar',
        ],
      ]),
      [
        'honorare' => [
          [
            '_identifier' => 'test',
            'stunden' => 2.0,
            'verguetung' => 22.0,
            'leistung' => 'foo',
            'qualifikation' => 'bar',
            'betrag' => 44.0,
          ],
        ],
      ],
    ];

    yield [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'fahrtkosten/intern',
        'type' => 'fahrtkosten/intern',
        'amount' => 123,
        'properties' => [],
      ]),
      ['fahrtkosten' => ['intern' => 123.0]],
    ];

    yield [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'fahrtkosten/anTeilnehmerErstattet',
        'type' => 'fahrtkosten/anTeilnehmerErstattet',
        'amount' => 123,
        'properties' => [],
      ]),
      ['fahrtkosten' => ['anTeilnehmerErstattet' => 123.0]],
    ];

    yield [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'test',
        'type' => 'sachkosten/ausstattung',
        'amount' => 123,
        'properties' => [
          'gegenstand' => 'foo',
        ],
      ]),
      [
        'sachkosten' => [
          'ausstattung' => [
            [
              '_identifier' => 'test',
              'gegenstand' => 'foo',
              'betrag' => 123.0,
            ],
          ],
        ],
      ],
    ];

    yield [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'test',
        'type' => 'sonstigeAusgabe',
        'amount' => 123,
        'properties' => [
          'zweck' => 'foo',
        ],
      ]),
      [
        'sonstigeAusgaben' => [
          [
            '_identifier' => 'test',
            'zweck' => 'foo',
            'betrag' => 123.0,
          ],
        ],
      ],
    ];

    yield [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 1,
        'identifier' => 'versicherung/teilnehmer',
        'type' => 'versicherung/teilnehmer',
        'amount' => 123,
        'properties' => [],
      ]),
      ['versicherung' => ['teilnehmer' => 123.0]],
    ];
  }

  /**
   * @phpstan-param array<ApplicationCostItemEntity> $items
   */
  private function mockCostItemManager(ApplicationProcessEntity $applicationProcess, array $items): void {
    $this->costItemManagerMock->method('getByApplicationProcessId')
      ->with($applicationProcess->getId())
      ->willReturn($items);
  }

}
