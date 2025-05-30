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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions;

use Civi\Funding\ApplicationProcess\ActionStatusInfo\AbstractApplicationProcessActionStatusInfoDecorator;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\DefaultApplicationProcessActionStatusInfo;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;

final class HiHApplicationActionStatusInfo extends AbstractApplicationProcessActionStatusInfoDecorator {

  use HiHSupportedFundingCaseTypesTrait;

  public function __construct() {
    parent::__construct(new DefaultApplicationProcessActionStatusInfo());
  }

  public function getFinalIneligibleStatusList(): array {
    return [
      'rejected',
      'rejected_after_advisory',
      'withdrawn',
    ];
  }

  public function getEligibleStatusList(): array {
    return ['advisory', 'approved', 'approved_partial', 'complete'];
  }

  public function isEligibleStatus(string $status): ?bool {
    if (in_array($status, $this->getEligibleStatusList(), TRUE)) {
      return TRUE;
    }

    if (in_array($status, $this->getFinalIneligibleStatusList(), TRUE)) {
      return FALSE;
    }

    return NULL;
  }

}
