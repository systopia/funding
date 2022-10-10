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

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\AVK1ApplicationResourcesItemsFactory
 */
final class AVK1ApplicationResourcesItemsFactoryTest extends TestCase {

  private AVK1ApplicationResourcesItemsFactory $resourcesItemsFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemsFactory = new AVK1ApplicationResourcesItemsFactory();
  }

  public function testCreateItems(): void {
    $expectedItems = [
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'teilnehmerbeitraege',
        'type' => 'teilnehmerbeitraege',
        'amount' => 100.0,
        'properties' => [],
      ]),
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'eigenmittel',
        'type' => 'eigenmittel',
        'amount' => 10.0,
        'properties' => [],
      ]),
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'oeffentlicheMittel/europa',
        'type' => 'oeffentlicheMittel',
        'amount' => 1.11,
        'properties' => [],
      ]),
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'oeffentlicheMittel/bundeslaender',
        'type' => 'oeffentlicheMittel',
        'amount' => 2.22,
        'properties' => [],
      ]),
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'oeffentlicheMittel/staedteUndKreise',
        'type' => 'oeffentlicheMittel',
        'amount' => 3.33,
        'properties' => [],
      ]),
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 's1',
        'type' => 'sonstigeMittel',
        'amount' => 1.0,
        'properties' => ['quelle' => 'Quelle 1'],
      ]),
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 's2',
        'type' => 'sonstigeMittel',
        'amount' => 2.0,
        'properties' => ['quelle' => 'Quelle 2'],
      ]),
    ];

    static::assertEquals(
      $expectedItems,
      $this->resourcesItemsFactory->createItems($this->createApplicationProcess())
    );
  }

  private function createApplicationProcess(): ApplicationProcessEntity {
    $now = date('Y-m-d H:i:s');
    $finanzierung = [
      'teilnehmerbeitraege' => 100.00,
      'eigenmittel' => 10.00,
      'oeffentlicheMittel' => [
        'europa' => 1.11,
        'bundeslaender' => 2.22,
        'staedteUndKreise' => 3.33,
      ],
      'sonstigeMittel' => [
        [
          '_identifier' => 's1',
          'betrag' => 1.0,
          'quelle' => 'Quelle 1',
        ],
        [
          '_identifier' => 's2',
          'betrag' => 2.0,
          'quelle' => 'Quelle 2',
        ],
      ],
    ];

    return ApplicationProcessEntity::fromArray([
      'id' => 2,
      'funding_case_id' => 3,
      'status' => 'new_status',
      'title' => 'Title',
      'short_description' => 'Description',
      'request_data' => ['finanzierung' => $finanzierung],
      'amount_requested' => 1.2,
      'creation_date' => $now,
      'modification_date' => $now,
      'start_date' => NULL,
      'end_date' => NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'is_review_calculative' => NULL,
    ]);
  }

}