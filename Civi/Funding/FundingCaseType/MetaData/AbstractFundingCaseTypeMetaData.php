<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseType\MetaData;

abstract class AbstractFundingCaseTypeMetaData implements FundingCaseTypeMetaDataInterface {

  public function getApplicationProcessStatus(string $name): ?ApplicationProcessStatus {
    return $this->getApplicationProcessStatuses()[$name] ?? NULL;
  }

  public function getCostItemType(string $name): ?CostItemTypeInterface {
    return $this->getCostItemTypes()[$name] ?? NULL;
  }

  public function getResourcesItemType(string $name): ?ResourcesItemTypeInterface {
    return $this->getResourcesItemTypes()[$name] ?? NULL;
  }

  public function isFinalDrawdownAcceptedByDefault(): bool {
    return TRUE;
  }

}
