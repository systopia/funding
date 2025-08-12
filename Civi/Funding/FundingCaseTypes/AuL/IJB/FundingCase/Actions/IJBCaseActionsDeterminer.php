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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\FundingCase\Actions;

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\FundingCase\Actions\AbstractFundingCaseActionsDeterminerDecorator;
use Civi\Funding\FundingCase\Actions\DefaultFundingCaseActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\Actions\IJBApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\IJB\IJBMetaData;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Traits\IJBSupportedFundingCaseTypesTrait;

final class IJBCaseActionsDeterminer extends AbstractFundingCaseActionsDeterminerDecorator {

  use IJBSupportedFundingCaseTypesTrait;

  public function __construct(
    IJBApplicationStatusDeterminer $applicationStatusDeterminer,
    ClearingProcessManager $clearingProcessManager,
    IJBMetaData $metaData
  ) {
    parent::__construct(new DefaultFundingCaseActionsDeterminer(
      $applicationStatusDeterminer, $clearingProcessManager, $metaData
    ));
  }

}
