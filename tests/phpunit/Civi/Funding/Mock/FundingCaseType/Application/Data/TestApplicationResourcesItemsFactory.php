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

namespace Civi\Funding\Mock\FundingCaseType\Application\Data;

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemsFactoryInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\Funding\Mock\FundingCaseType\Traits\TestSupportedFundingCaseTypesTrait;

final class TestApplicationResourcesItemsFactory implements ApplicationResourcesItemsFactoryInterface {

  use TestSupportedFundingCaseTypesTrait;

  public function addIdentifiers(array $requestData): array {
    return $requestData;
  }

  /**
   * @inheritDoc
   */
  public function areResourcesItemsChanged(array $requestData, array $previousRequestData): bool {
    return $requestData['resources'] !== $previousRequestData['resources'];
  }

  /**
   * @inheritDoc
   */
  public function createItems(ApplicationProcessEntity $applicationProcess): array {
    /** @var float $amount */
    $amount = $applicationProcess->getRequestData()['resources'];

    return [ApplicationResourcesItemEntity::fromArray([
      'application_process_id' => $applicationProcess->getId(),
      'identifier' => 'resources',
      'type' => 'amount',
      'amount' => $amount,
      'properties' => [],
    ]),
    ];
  }

}
