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

/**
 * @phpstan-type zuschussT array{
 *   teilnehmerkosten: float,
 *   honorarkosten: float,
 *   fahrtkosten: float,
 *   zuschlag: float,
 * }
 */
class IJBFormDataZuschussFactory {

  private ApplicationResourcesItemManager $resourcesItemManager;

  public function __construct(ApplicationResourcesItemManager $resourcesItemManager) {
    $this->resourcesItemManager = $resourcesItemManager;
  }

  /**
   * @phpstan-return zuschussT
   *
   * @throws \CRM_Core_Exception
   */
  public function createZuschuss(ApplicationProcessEntity $applicationProcess): array {
    $zuschuss = [
      'teilnehmerkosten' => 0.0,
      'honorarkosten' => 0.0,
      'fahrtkosten' => 0.0,
      'zuschlag' => 0.0,
    ];

    $items = $this->resourcesItemManager->getByApplicationProcessId($applicationProcess->getId());
    foreach ($items as $item) {
      [$type, $subType] = explode('/', $item->getType(), 2) + [NULL, NULL];
      if ('zuschuss' === $type && isset($zuschuss[$subType])) {
        $zuschuss[$subType] = $item->getAmount();
        /** @phpstan-var zuschussT $zuschuss */
      }
    }

    return $zuschuss;
  }

}
