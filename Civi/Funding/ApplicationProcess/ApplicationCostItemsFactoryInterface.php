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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\Entity\ApplicationProcessEntity;

interface ApplicationCostItemsFactoryInterface {

  /**
   * @phpstan-return array<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  /**
   * Adds identifiers to new cost items in request data, where necessary. This
   * identifiers can later be used in createItems(). These identifiers allow
   * to make changes to already persisted cost items only where necessary.
   *
   * @param array<string, mixed> $requestData
   *
   * @return array<string, mixed>
   *
   * @see createItems()
   */
  public function addIdentifiers(array $requestData): array;

  /**
   * @param array<string, mixed> $requestData
   * @param array<string, mixed> $previousRequestData
   *
   * @return bool true if persisted cost items need to be updated.
   */
  public function areCostItemsChanged(array $requestData, array $previousRequestData): bool;

  /**
   * Creates objects of type ApplicationCostItemEntity from the request data.
   *
   * @phpstan-return array<\Civi\Funding\Entity\ApplicationCostItemEntity>
   *
   * @see ApplicationProcessEntity::getRequestData()
   */
  public function createItems(ApplicationProcessEntity $applicationProcess): array;

}
