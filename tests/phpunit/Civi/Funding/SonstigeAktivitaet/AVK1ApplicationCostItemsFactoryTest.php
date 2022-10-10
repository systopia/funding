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

use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\AVK1ApplicationCostItemsFactory
 */
final class AVK1ApplicationCostItemsFactoryTest extends TestCase {

  private AVK1ApplicationCostItemsFactory $costItemsFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->costItemsFactory = new AVK1ApplicationCostItemsFactory();
  }

  public function testCreateItems(): void {
    $expectedItems = [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'unterkunftUndVerpflegung',
        'type' => 'unterkunftUndVerpflegung',
        'amount' => 222.22,
        'properties' => [],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'h1',
        'type' => 'honorar',
        'amount' => 246.64,
        'properties' => [
          'stunden' => 11.1,
          'verguetung' => 22.22,
          'zweck' => 'Honorar 1',
        ],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'h2',
        'type' => 'honorar',
        'amount' => 99.0,
        'properties' => [
          'stunden' => 9.9,
          'verguetung' => 10,
          'zweck' => 'Honorar 2',
        ],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'fahrtkosten/intern',
        'type' => 'fahrtkosten',
        'amount' => 2.2,
        'properties' => [],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'fahrtkosten/anTeilnehmerErstattet',
        'type' => 'fahrtkosten',
        'amount' => 3.3,
        'properties' => [],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'sachkosten/haftungKfz',
        'type' => 'sachkosten',
        'amount' => 4.4,
        'properties' => [],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'a1',
        'type' => 'sachkosten',
        'amount' => 5.5,
        'properties' => ['gegenstand' => 'Thing1'],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'a2',
        'type' => 'sachkosten',
        'amount' => 6.6,
        'properties' => ['gegenstand' => 'Thing2'],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 's1',
        'type' => 'sonstigeAusgaben',
        'amount' => 12.34,
        'properties' => ['zweck' => 'Sonstige Ausgaben 1'],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 's2',
        'type' => 'sonstigeAusgaben',
        'amount' => 56.78,
        'properties' => ['zweck' => 'Sonstige Ausgaben 2'],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'versicherungTeilnehmer',
        'type' => 'versicherungTeilnehmer',
        'amount' => 9.9,
        'properties' => [],
      ]),
    ];

    static::assertEquals(
      $expectedItems,
      $this->costItemsFactory->createItems($this->createApplicationProcess())
    );
  }

  private function createApplicationProcess(): ApplicationProcessEntity {
    $kosten = [
      'unterkunftUndVerpflegung' => 222.22,
      'honorare' => [
        [
          '_identifier' => 'h1',
          'stunden' => 11.1,
          'verguetung' => 22.22,
          'zweck' => 'Honorar 1',
          'betrag' => 246.64,
        ],
        [
          '_identifier' => 'h2',
          'stunden' => 9.9,
          'verguetung' => 10,
          'zweck' => 'Honorar 2',
          'betrag' => 99.0,
        ],
      ],
      'fahrtkosten' => [
        'intern' => 2.2,
        'anTeilnehmerErstattet' => 3.3,
      ],
      'sachkosten' => [
        'haftungKfz' => 4.4,
        'ausstattung' => [
          [
            '_identifier' => 'a1',
            'gegenstand' => 'Thing1',
            'betrag' => 5.5,
          ],
          [
            '_identifier' => 'a2',
            'gegenstand' => 'Thing2',
            'betrag' => 6.6,
          ],
        ],
      ],
      'sonstigeAusgaben' => [
        [
          '_identifier' => 's1',
          'betrag' => 12.34,
          'zweck' => 'Sonstige Ausgaben 1',
        ],
        [
          '_identifier' => 's2',
          'betrag' => 56.78,
          'zweck' => 'Sonstige Ausgaben 2',
        ],
      ],
      'versicherungTeilnehmer' => 9.9,
    ];

    return ApplicationProcessFactory::createApplicationProcess([
      'request_data' => ['kosten' => $kosten],
    ]);
  }

}
