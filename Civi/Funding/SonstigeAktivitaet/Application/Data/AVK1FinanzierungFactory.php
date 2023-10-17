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

namespace Civi\Funding\SonstigeAktivitaet\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
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
 *     _identifier: string,
 *     quelle: string,
 *     betrag: float,
 *   }>,
 * }
 */
class AVK1FinanzierungFactory {

  private ApplicationResourcesItemManager $resourcesItemManager;

  public function __construct(ApplicationResourcesItemManager $resourcesItemManager) {
    $this->resourcesItemManager = $resourcesItemManager;
  }

  /**
   * @phpstan-return finanzierungT
   */
  public function createFinanzierung(ApplicationProcessEntity $applicationProcess): array {
    $finanzierung = [
      'teilnehmerbeitraege' => 0.0,
      'eigenmittel' => 0.0,
      'oeffentlicheMittel' => [
        'europa' => 0.0,
        'bundeslaender' => 0.0,
        'staedteUndKreise' => 0.0,
      ],
      'sonstigeMittel' => [],
    ];

    $items = $this->resourcesItemManager->getByApplicationProcessId($applicationProcess->getId());
    foreach ($items as $item) {
      [$type, $subType] = explode('/', $item->getType(), 2) + [NULL, NULL];
      if ('teilnehmerbeitraege' === $type) {
        $finanzierung['teilnehmerbeitraege'] = $item->getAmount();
      }
      elseif ('eigenmittel' === $type) {
        $finanzierung['eigenmittel'] = $item->getAmount();
      }
      elseif ('oeffentlicheMittel' === $type) {
        if (in_array($subType, ['europa', 'bundeslaender', 'staedteUndKreise'], TRUE)) {
          $finanzierung['oeffentlicheMittel'][$subType] = $item->getAmount();
        }
      }
      elseif ('sonstigeMittel' === $type) {
        $quelle = $item->getProperties()['quelle'];
        Assert::string($quelle);
        $finanzierung['sonstigeMittel'][] = [
          '_identifier' => $item->getIdentifier(),
          'quelle' => $quelle,
          'betrag' => $item->getAmount(),
        ];
      }
    }

    return $finanzierung;
  }

}
