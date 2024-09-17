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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase\Actions;

use Civi\Funding\FundingCase\Actions\AbstractFundingCaseActionsDeterminerDecorator;
use Civi\Funding\FundingCase\Actions\DefaultFundingCaseActionsDeterminer;
use Civi\Funding\FundingCase\Actions\SetRecipientContactActionsDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationActionStatusInfo;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;

final class HiHCaseActionsDeterminer extends AbstractFundingCaseActionsDeterminerDecorator {

  use HiHSupportedFundingCaseTypesTrait;

  public function __construct(HiHApplicationActionStatusInfo $statusInfo) {
    parent::__construct(new SetRecipientContactActionsDeterminer(new DefaultFundingCaseActionsDeterminer($statusInfo)));
  }

}
