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

namespace Civi\Funding\SammelantragKurs\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

/**
 * @phpstan-type zuschussT array{
 *   teilnehmerkostenMax: float,
 *   teilnehmerkosten: float|null,
 *   fahrtkostenMax: float,
 *   fahrtkosten: float|null,
 *   honorarkostenMax: float,
 *   honorarKosten: float|null,
 * }
 */
final class KursApplicationCostItemsFactory implements ApplicationCostItemsFactoryInterface {

  use KursSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function addIdentifiers(array $requestData): array {
    return $requestData;
  }

  /**
   * @inheritDoc
   */
  public function areCostItemsChanged(array $requestData, array $previousRequestData): bool {
    return $requestData['zuschuss'] != $previousRequestData['zuschuss'];
  }

  /**
   * @inheritDoc
   */
  public function createItems(ApplicationProcessEntity $applicationProcess): array {
    /** @phpstan-var zuschussT $zuschuss */
    $zuschuss = $applicationProcess->getRequestData()['zuschuss'];

    return [
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'teilnehmerkosten',
        'type' => 'teilnehmerkosten',
        'amount' => $zuschuss['teilnehmerkosten'] ?? $zuschuss['teilnehmerkostenMax'],
        'properties' => [],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'fahrtkosten',
        'type' => 'fahrtkosten',
        'amount' => $zuschuss['fahrtkosten'] ?? $zuschuss['fahrtkostenMax'],
        'properties' => [],
      ]),
      ApplicationCostItemEntity::fromArray([
        'application_process_id' => $applicationProcess->getId(),
        'identifier' => 'honorarkosten',
        'type' => 'honorarkosten',
        'amount' => $zuschuss['honorarkosten'] ?? $zuschuss['honorarkostenMax'],
        'properties' => [],
      ]),
    ];
  }

}
