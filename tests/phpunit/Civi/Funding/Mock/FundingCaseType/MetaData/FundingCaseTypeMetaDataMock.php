<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\Mock\FundingCaseType\MetaData;

use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;

final class FundingCaseTypeMetaDataMock extends AbstractFundingCaseTypeMetaData {

  public string $name;

  /**
   * @phpstan-var array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus>
   */
  public $applicationProcessStatuses = [];

  /**
   * @phpstan-var array<string, \Civi\Funding\FundingCaseType\MetaData\CostItemTypeInterface>
   */
  public array $cosItemTypes = [];

  /**
   * @phpstan-var array<string, \Civi\Funding\FundingCaseType\MetaData\ResourcesItemTypeInterface>
   */
  private array $resourcesItemTypes = [];

  public function __construct(string $name = 'mock') {
    $this->name = $name;
  }

  public function getName(): string {
    return $this->name;
  }

  public function addApplicationProcessStatus(ApplicationProcessStatus $status): self {
    $this->applicationProcessStatuses[$status->getName()] = $status;

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function getApplicationProcessStatuses(): array {
    // @phpstan-ignore return.type
    return $this->applicationProcessStatuses;
  }

  /**
   * @inheritDoc
   */
  public function getCostItemTypes(): array {
    return $this->cosItemTypes;
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemTypes(): array {
    return $this->resourcesItemTypes;
  }

}
