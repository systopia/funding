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

namespace Civi\Funding\SonstigeAktivitaet\Data;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ApplicationResourcesItemsFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ApplicationResourcesItemsFactory
 */
final class AVK1ApplicationResourcesItemsFactoryTest extends TestCase {

  private AVK1ApplicationResourcesItemsFactory $resourcesItemsFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->resourcesItemsFactory = new AVK1ApplicationResourcesItemsFactory();
  }

  public function testAddIdentifiers(): void {
    $requestData = [
      'finanzierung' => [
        'sonstigeMittel' => [
          ['_identifier' => '', 'quelle' => 'Test', 'betrag' => 1.23],
        ],
      ],
    ];
    $result = $this->resourcesItemsFactory->addIdentifiers($requestData);

    // @phpstan-ignore-next-line
    static::assertNotEmpty($result['finanzierung']['sonstigeMittel'][0]['_identifier']);
  }

  public function testAreIdentifiersChanged(): void {
    static::assertTrue($this->resourcesItemsFactory->areResourcesItemsChanged(
      ['finanzierung' => ['foo' => 1]], ['finanzierung' => ['foo' => 2]]
    ));
    static::assertFalse($this->resourcesItemsFactory->areResourcesItemsChanged(
      ['finanzierung' => ['foo' => 1]], ['finanzierung' => ['foo' => 1]]
    ));
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
        'type' => 'oeffentlicheMittel/europa',
        'amount' => 1.11,
        'properties' => [],
      ]),
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'oeffentlicheMittel/bundeslaender',
        'type' => 'oeffentlicheMittel/bundeslaender',
        'amount' => 2.22,
        'properties' => [],
      ]),
      ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => 2,
        'identifier' => 'oeffentlicheMittel/staedteUndKreise',
        'type' => 'oeffentlicheMittel/staedteUndKreise',
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

    return ApplicationProcessFactory::createApplicationProcess([
      'request_data' => ['finanzierung' => $finanzierung],
    ]);
  }

}
