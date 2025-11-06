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

namespace Civi\Funding\Mock\FundingCaseType;

use Civi\Funding\FundingCaseType\MetaData\AbstractFundingCaseTypeMetaData;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemType;
use Civi\Funding\FundingCaseType\MetaData\ReworkApplicationProcessStatuses;

final class TestCaseTypeMetaData extends AbstractFundingCaseTypeMetaData {

  public const NAME = 'TestCaseType';

  /**
   * @phpstan-var array<string, CostItemType>
   */
  private ?array $costItemTypes = NULL;

  /**
   * @phpstan-var array<string, ResourcesItemType>
   */
  private ?array $resourcesItemTypes = NULL;

  private ?bool $generalClearingAdmitAllowed = NULL;

  public function getName(): string {
    return self::NAME;
  }

  /**
   * @inheritDoc
   */
  public function getApplicationProcessStatuses(): array {
    return DefaultApplicationProcessStatuses::getAll() + ReworkApplicationProcessStatuses::getAll();
  }

  /**
   * @inheritDoc
   */
  public function getCostItemTypes(): array {
    return $this->costItemTypes ??= [
      'amount' => new CostItemType('amount', 'Amount requested'),
    ];
  }

  /**
   * @inheritDoc
   */
  public function getResourcesItemTypes(): array {
    return $this->resourcesItemTypes ??= [
      'testResources' => new ResourcesItemType('testResources', 'Test resources'),
    ];
  }

  public function isGeneralClearingAdmitAllowed(): bool {
    return $this->generalClearingAdmitAllowed ?? parent::isGeneralClearingAdmitAllowed();
  }

  public function setGeneralClearingAdmitAllowed(?bool $generalClearingAdmitAllowed): self {
    $this->generalClearingAdmitAllowed = $generalClearingAdmitAllowed;

    return $this;
  }

}
