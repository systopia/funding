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

namespace Civi\Funding\IJB\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\ItemsIdentifierUtil;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type kostenT array{
 *   unterkunftUndVerpflegung: float,
 *   honorare: array<array{
 *      _identifier?: string,
 *      stunden: float,
 *      verguetung: float,
 *      leistung: string,
 *      qualifikation: string,
 *      betrag: float,
 *    }>,
 *    fahrtkosten: array{
 *      flug: float,
 *      programm: float,
 *      anTeilnehmerErstattet: float,
 *    },
 *    programmkosten: array{
 *      programmkosten: float,
 *      arbeitsmaterial: float,
 *    },
 *    sonstigeKosten: array<array{
 *      _identifier?: string,
 *      gegenstand: string,
 *      betrag: float,
 *    }>,
 *    sonstigeAusgaben: array<array{
 *      _identifier?: string,
 *      zweck: string,
 *      betrag: float,
 *    }>,
 *    zuschlagsrelevanteKosten: array{
 *      programmabsprachen: float,
 *      vorbereitungsmaterial: float,
 *      veroeffentlichungen: float,
 *      honorare: float,
 *      fahrtkostenUndVerpflegung: float,
 *      reisekosten: float,
 *      miete: float,
 *    },
 * }
 */
final class IJBApplicationCostItemsFactory implements ApplicationCostItemsFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function addIdentifiers(array $requestData): array {
    /** @phpstan-var kostenT $kosten */
    $kosten = &$requestData['kosten'];
    $kosten['honorare'] = ItemsIdentifierUtil::addIdentifiers($kosten['honorare']);
    $kosten['sonstigeKosten'] = ItemsIdentifierUtil::addIdentifiers($kosten['sonstigeKosten']);
    $kosten['sonstigeAusgaben'] = ItemsIdentifierUtil::addIdentifiers($kosten['sonstigeAusgaben']);

    return $requestData;
  }

  /**
   * @inheritDoc
   */
  public function areCostItemsChanged(array $requestData, array $previousRequestData): bool {
    return $requestData['kosten'] != $previousRequestData['kosten'];
  }

  /**
   * @inheritDoc
   */
  public function createItems(ApplicationProcessEntity $applicationProcess): array {
    /** @phpstan-var kostenT $kosten */
    $kosten = $applicationProcess->getRequestData()['kosten'];

    $items = [];

    $keys = [
      'unterkunftUndVerpflegung' => NULL,
      'fahrtkosten' => [
        'flug',
        'programm',
        'anTeilnehmerErstattet',
      ],
      'programmkosten' => [
        'programmkosten',
        'arbeitsmaterial',
      ],
      'zuschlagsrelevanteKosten' => [
        'programmabsprachen',
        'vorbereitungsmaterial',
        'veroeffentlichungen',
        'honorare',
        'fahrtkostenUndVerpflegung',
        'reisekosten',
        'miete',
      ],
    ];

    foreach ($keys as $key => $subKeys) {
      if (NULL === $subKeys) {
        /** @var float $amount */
        $amount = $kosten[$key];
        if ($amount > 0) {
          $items[] = ApplicationCostItemEntity::fromArray([
            'application_process_id' => $applicationProcess->getId(),
            'identifier' => $key,
            'type' => $key,
            'amount' => $amount,
            'properties' => [],
          ]);
        }
      }
      else {
        foreach ($subKeys as $subKey) {
          /** @var float $amount */
          // @phpstan-ignore-next-line
          $amount = $kosten[$key][$subKey];
          if ($amount > 0) {
            $items[] = ApplicationCostItemEntity::fromArray([
              'application_process_id' => $applicationProcess->getId(),
              'identifier' => $key . '/' . $subKey,
              'type' => $key . '/' . $subKey,
              'amount' => $amount,
              'properties' => [],
            ]);
          }
        }
      }
    }

    foreach ($kosten['honorare'] as $honorar) {
      Assert::keyExists($honorar, '_identifier');
      $items[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => $honorar['_identifier'],
        'type' => 'honorar',
        'amount' => $honorar['betrag'],
        'properties' => [
          'stunden' => $honorar['stunden'],
          'verguetung' => $honorar['verguetung'],
          'leistung' => $honorar['leistung'],
          'qualifikation' => $honorar['qualifikation'],
        ],
      ]);
    }

    foreach ($kosten['sonstigeKosten'] as $sonstigeKosten) {
      Assert::keyExists($sonstigeKosten, '_identifier');
      $items[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => $sonstigeKosten['_identifier'],
        'type' => 'sonstigeKosten',
        'amount' => $sonstigeKosten['betrag'],
        'properties' => [
          'gegenstand' => $sonstigeKosten['gegenstand'],
        ],
      ]);
    }

    foreach ($kosten['sonstigeAusgaben'] as $sonstigeAusgaben) {
      Assert::keyExists($sonstigeAusgaben, '_identifier');
      $items[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => $sonstigeAusgaben['_identifier'],
        'type' => 'sonstigeAusgabe',
        'amount' => $sonstigeAusgaben['betrag'],
        'properties' => [
          'zweck' => $sonstigeAusgaben['zweck'],
        ],
      ]);
    }

    return $items;
  }

}
