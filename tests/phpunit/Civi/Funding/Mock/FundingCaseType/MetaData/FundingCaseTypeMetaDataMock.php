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

use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseAction;

final class FundingCaseTypeMetaDataMock extends AbstractFundingCaseTypeMetaData {

  public string $name;

  /**
   * @var array<string, ApplicationProcessAction>
   */
  public array $applicationProcessActions = [];

  /**
   * @var array<string, ApplicationProcessStatus>
   */
  public array $applicationProcessStatuses = [];

  /**
   * @var array<string, FundingCaseAction>
   */
  public array $fundingCaseActions = [];

  public bool $finalDrawdownAcceptedByDefault = TRUE;

  /**
   * @var array<string, \Civi\Funding\FundingCaseType\MetaData\CostItemTypeInterface>
   */
  public array $cosItemTypes = [];

  /**
   * @var array<string, \Civi\Funding\FundingCaseType\MetaData\ResourcesItemTypeInterface>
   */
  public array $resourcesItemTypes = [];

  public function __construct(string $name = FundingCaseTypeFactory::DEFAULT_NAME) {
    $this->name = $name;
  }

  public function getName(): string {
    return $this->name;
  }

  public function addApplicationProcessAction(ApplicationProcessAction $action): void {
    $this->applicationProcessActions[$action->getName()] = $action;
  }

  public function getApplicationProcessActions(): array {
    // @phpstan-ignore return.type
    return $this->applicationProcessActions;
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

  public function addFundingCaseAction(FundingCaseAction $action): void {
    $this->fundingCaseActions[$action->getName()] = $action;
  }

  /**
   * @inheritDoc
   */
  public function getFundingCaseActions(): array {
    return $this->fundingCaseActions;
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemTypes(): array {
    return $this->resourcesItemTypes;
  }

  /**
   * @inheritDoc
   */
  public function isFinalDrawdownAcceptedByDefault(): bool {
    return $this->finalDrawdownAcceptedByDefault;
  }

}
