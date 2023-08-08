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

namespace Civi\Funding\Mock\Form\FundingCaseType;

use Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Mock\Form\FundingCaseType\Traits\TestSupportedFundingCaseTypesTrait;

final class TestApplicationCostItemsFactory implements ApplicationCostItemsFactoryInterface {

  use TestSupportedFundingCaseTypesTrait;

  public function addIdentifiers(array $requestData): array {
    return $requestData;
  }

  /**
   * @inheritDoc
   */
  public function areCostItemsChanged(array $requestData, array $previousRequestData): bool {
    return $requestData['amountRequested'] !== $previousRequestData['amountRequested'];
  }

  /**
   * @inheritDoc
   */
  public function createItems(ApplicationProcessEntity $applicationProcess): array {
    return [ApplicationCostItemEntity::fromArray([
      'application_process_id' => $applicationProcess->getId(),
      'identifier' => 'amountRequested',
      'type' => 'amount',
      'amount' => $applicationProcess->getAmountRequested(),
      'properties' => [],
    ]),
    ];
  }

}
