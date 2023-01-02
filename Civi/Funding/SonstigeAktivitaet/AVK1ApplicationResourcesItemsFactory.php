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

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\ItemsIdentifierUtil;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type finanzierungT array{
 *   teilnehmerbeitraege: float,
 *   eigenmittel: float,
 *   oeffentlicheMittel: array{
 *     europa: float,
 *     bundeslaender: float,
 *     staedteUndKreise: float,
 *   },
 *   sonstigeMittel: array<array{
 *     _identifier?: string,
 *     quelle: string,
 *     betrag: float,
 *   }>,
 * }
 */
class AVK1ApplicationResourcesItemsFactory implements ApplicationResourcesItemsFactoryInterface {

  public static function getSupportedFundingCaseTypes(): array {
    return ['AVK1SonstigeAktivitaet'];
  }

  /**
   * @inheritDoc
   */
  public function addIdentifiers(array $requestData): array {
    /** @phpstan-var finanzierungT $finanzierung */
    $finanzierung = &$requestData['finanzierung'];
    $finanzierung['sonstigeMittel'] = ItemsIdentifierUtil::addIdentifiers($finanzierung['sonstigeMittel']);

    return $requestData;
  }

  /**
   * @inheritDoc
   */
  public function areResourcesItemsChanged(array $requestData, array $previousRequestData): bool {
    return $requestData['finanzierung'] != $previousRequestData['finanzierung'];
  }

  /**
   * @phpstan-return array<ApplicationResourcesItemEntity>
   */
  public function createItems(ApplicationProcessEntity $applicationProcess): array {
    /** @phpstan-var finanzierungT $finanzierung */
    $finanzierung = $applicationProcess->getRequestData()['finanzierung'];

    $items = [];

    if ($finanzierung['teilnehmerbeitraege'] > 0) {
      $items[] = ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'teilnehmerbeitraege',
        'type' => 'teilnehmerbeitraege',
        'amount' => $finanzierung['teilnehmerbeitraege'],
        'properties' => [],
      ]);
    }

    if ($finanzierung['eigenmittel'] > 0) {
      $items[] = ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'eigenmittel',
        'type' => 'eigenmittel',
        'amount' => $finanzierung['eigenmittel'],
        'properties' => [],
      ]);
    }

    if ($finanzierung['oeffentlicheMittel']['europa'] > 0) {
      $items[] = ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'oeffentlicheMittel/europa',
        'type' => 'oeffentlicheMittel/europa',
        'amount' => $finanzierung['oeffentlicheMittel']['europa'],
        'properties' => [],
      ]);
    }

    if ($finanzierung['oeffentlicheMittel']['bundeslaender'] > 0) {
      $items[] = ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'oeffentlicheMittel/bundeslaender',
        'type' => 'oeffentlicheMittel/bundeslaender',
        'amount' => $finanzierung['oeffentlicheMittel']['bundeslaender'],
        'properties' => [],
      ]);
    }

    if ($finanzierung['oeffentlicheMittel']['staedteUndKreise'] > 0) {
      $items[] = ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'oeffentlicheMittel/staedteUndKreise',
        'type' => 'oeffentlicheMittel/staedteUndKreise',
        'amount' => $finanzierung['oeffentlicheMittel']['staedteUndKreise'],
        'properties' => [],
      ]);
    }

    foreach ($finanzierung['sonstigeMittel'] as $sonstigeMittel) {
      Assert::keyExists($sonstigeMittel, '_identifier');
      $items[] = ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => $sonstigeMittel['_identifier'],
        'type' => 'sonstigeMittel',
        'amount' => $sonstigeMittel['betrag'],
        'properties' => [
          'quelle' => $sonstigeMittel['quelle'],
        ],
      ]);
    }

    return $items;
  }

}
