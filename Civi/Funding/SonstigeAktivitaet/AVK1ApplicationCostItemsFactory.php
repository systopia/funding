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

use Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\ItemsIdentifierUtil;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type kostenT array{
 *   unterkunftUndVerpflegung: float,
 *   honorare: array<array{
 *     _identifier?: string,
 *     stunden: float,
 *     verguetung: float,
 *     leistung: string,
 *     qualifikation: string,
 *     betrag: float,
 *   }>,
 *   fahrtkosten: array{
 *     intern: float,
 *     anTeilnehmerErstattet: float,
 *   },
 *   sachkosten: array{
 *     ausstattung: array<array{
 *       _identifier?: string,
 *       gegenstand: string,
 *       betrag: float,
 *     }>,
 *   },
 *   sonstigeAusgaben: array<array{
 *     _identifier?: string,
 *     betrag: float,
 *     zweck: string,
 *   }>,
 *   versicherung: array{
 *     teilnehmer: float,
 *   }
 * }
 */
class AVK1ApplicationCostItemsFactory implements ApplicationCostItemsFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function addIdentifiers(array $requestData): array {
    /** @phpstan-var kostenT $kosten */
    $kosten = &$requestData['kosten'];
    $kosten['honorare'] = ItemsIdentifierUtil::addIdentifiers($kosten['honorare']);
    $kosten['sachkosten']['ausstattung'] = ItemsIdentifierUtil::addIdentifiers($kosten['sachkosten']['ausstattung']);
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

    if ($kosten['unterkunftUndVerpflegung'] > 0) {
      $items[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'unterkunftUndVerpflegung',
        'type' => 'unterkunftUndVerpflegung',
        'amount' => $kosten['unterkunftUndVerpflegung'],
        'properties' => [],
      ]);
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

    if ($kosten['fahrtkosten']['intern'] > 0) {
      $items[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'fahrtkosten/intern',
        'type' => 'fahrtkosten/intern',
        'amount' => $kosten['fahrtkosten']['intern'],
        'properties' => [],
      ]);
    }

    if ($kosten['fahrtkosten']['anTeilnehmerErstattet'] > 0) {
      $items[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'fahrtkosten/anTeilnehmerErstattet',
        'type' => 'fahrtkosten/anTeilnehmerErstattet',
        'amount' => $kosten['fahrtkosten']['anTeilnehmerErstattet'],
        'properties' => [],
      ]);
    }

    foreach ($kosten['sachkosten']['ausstattung'] as $ausstattung) {
      Assert::keyExists($ausstattung, '_identifier');
      $items[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => $ausstattung['_identifier'],
        'type' => 'sachkosten/ausstattung',
        'amount' => $ausstattung['betrag'],
        'properties' => [
          'gegenstand' => $ausstattung['gegenstand'],
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

    if ($kosten['versicherung']['teilnehmer'] > 0) {
      $items[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'versicherung/teilnehmer',
        'type' => 'versicherung/teilnehmer',
        'amount' => $kosten['versicherung']['teilnehmer'],
        'properties' => [],
      ]);
    }

    return $items;
  }

}
