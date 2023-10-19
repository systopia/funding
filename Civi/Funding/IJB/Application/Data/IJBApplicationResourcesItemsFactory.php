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

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\ItemsIdentifierUtil;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\Funding\IJB\Traits\IJBSupportedFundingCaseTypesTrait;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type finanzierungT array{
 *    teilnehmerbeitraege: float,
 *    eigenmittel: float,
 *    oeffentlicheMittel: array{
 *      europa: float,
 *      bundeslaender: float,
 *      staedteUndKreise: float,
 *    },
 *    sonstigeMittel: array<array{
 *      _identifier?: string,
 *      quelle: string,
 *      betrag: float,
 *    }>,
 *  }
 *
 * @phpstan-type zuschussT array{
 *   teilnehmerkosten: float,
 *   honorarkosten: float,
 *   fahrtkosten: float,
 *   zuschlag: float,
 * }
 */
final class IJBApplicationResourcesItemsFactory implements ApplicationResourcesItemsFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

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
    return $requestData['finanzierung'] != $previousRequestData['finanzierung']
      || $requestData['zuschuss'] !== $previousRequestData['zuschuss'];
  }

  /**
   * @inheritDoc
   */
  public function createItems(ApplicationProcessEntity $applicationProcess): array {
    /** @phpstan-var finanzierungT $finanzierung */
    $finanzierung = $applicationProcess->getRequestData()['finanzierung'];

    $items = [];

    $finanzierungKeys = [
      'teilnehmerbeitraege' => NULL,
      'eigenmittel' => NULL,
      'oeffentlicheMittel' => [
        'europa',
        'bundeslaender',
        'staedteUndKreise',
      ],
    ];

    foreach ($finanzierungKeys as $key => $subKeys) {
      if (NULL === $subKeys) {
        /** @var float $amount */
        $amount = $finanzierung[$key];
        if ($amount > 0) {
          $items[] = ApplicationResourcesItemEntity::fromArray([
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
          $amount = $finanzierung[$key][$subKey];
          if ($amount > 0) {
            $items[] = ApplicationResourcesItemEntity::fromArray([
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

    /** @phpstan-var zuschussT $zuschuss */
    $zuschuss = $applicationProcess->getRequestData()['zuschuss'];

    $zuschussKeys = [
      'teilnehmerkosten',
      'honorarkosten',
      'fahrtkosten',
      'zuschlag',
    ];

    foreach ($zuschussKeys as $subType) {
      if ($zuschuss[$subType] > 0) {
        $items[] = ApplicationResourcesItemEntity::fromArray([
          'application_process_id' => $applicationProcess->getId(),
          'identifier' => 'zuschuss/' . $subType,
          'type' => 'zuschuss/' . $subType,
          'amount' => $zuschuss[$subType],
          'properties' => [],
        ]);
      }
    }

    return $items;
  }

}
