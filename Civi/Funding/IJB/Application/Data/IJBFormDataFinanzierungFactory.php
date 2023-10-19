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

namespace Civi\Funding\IJB\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type finanzierungT array{
 *     teilnehmerbeitraege: float,
 *     eigenmittel: float,
 *     oeffentlicheMittel: array{
 *       europa: float,
 *       bundeslaender: float,
 *       staedteUndKreise: float,
 *     },
 *     sonstigeMittel: array<array{
 *       _identifier?: string,
 *       quelle: string,
 *       betrag: float,
 *     }>,
 *   }
 */
class IJBFormDataFinanzierungFactory {

  private ApplicationResourcesItemManager $resourcesItemManager;

  public function __construct(ApplicationResourcesItemManager $resourcesItemManager) {
    $this->resourcesItemManager = $resourcesItemManager;
  }

  /**
   * @phpstan-return finanzierungT
   *
   * @throws \CRM_Core_Exception
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
      if (NULL !== $subType && is_array($finanzierung[$type] ?? NULL) && isset($finanzierung[$type][$subType])) {
        $finanzierung[$type][$subType] = $item->getAmount();
        /** @phpstan-var finanzierungT $finanzierung */
      }
      elseif (NULL === $subType && is_float($finanzierung[$type] ?? NULL)) {
        $finanzierung[$type] = $item->getAmount();
        /** @phpstan-var finanzierungT $finanzierung */
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
