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

namespace Civi\Funding\Mock\FundingCaseType\FundingCase\Actions;

use Civi\Funding\FundingCase\Actions\AbstractFundingCaseActionsDeterminerDecorator;
use Civi\Funding\FundingCase\Actions\DefaultFundingCaseActionsDeterminer;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Actions\SetRecipientContactActionsDeterminer;
use Civi\Funding\Mock\FundingCaseType\Application\Actions\TestApplicationActionStatusInfo;
use Civi\Funding\Mock\FundingCaseType\Traits\TestSupportedFundingCaseTypesTrait;
use Civi\Funding\Permission\Traits\HasReviewPermissionTrait;

final class TestCaseActionsDeterminer extends AbstractFundingCaseActionsDeterminerDecorator {

  use TestSupportedFundingCaseTypesTrait;

  use HasReviewPermissionTrait;

  public function __construct(
    TestApplicationActionStatusInfo $statusInfo
  ) {
    parent::__construct(new SetRecipientContactActionsDeterminer(new DefaultFundingCaseActionsDeterminer($statusInfo)));
  }

  public function getActions(string $status, array $applicationProcessStatusList, array $permissions): array {
    $actions = parent::getActions(
      $status,
      $applicationProcessStatusList,
      $permissions
    );

    if ('ongoing' === $status && $this->hasReviewPermission($permissions)) {
      $actions[] = FundingCaseActions::UPDATE_AMOUNT_APPROVED;
    }

    return $actions;
  }

}
