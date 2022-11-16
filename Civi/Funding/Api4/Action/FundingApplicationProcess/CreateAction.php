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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\DAOCreateAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\Traits\CheckReviewPermissionTrait;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;

final class CreateAction extends DAOCreateAction {

  use CheckReviewPermissionTrait;

  public function __construct(Api4Interface $api4, FundingCaseManager $fundingCaseManager) {
    parent::__construct(FundingApplicationProcess::_getEntityName(), 'create');
    $this->_api4 = $api4;
    $this->_fundingCaseManager = $fundingCaseManager;
  }

}