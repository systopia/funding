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

namespace Civi\Funding\Api4\Action\Remote\FundingCase;

use Civi\Api4\RemoteFundingCase;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Api4\Action\Traits\FundingCaseTypeIdParameterTrait;
use Civi\Funding\Api4\Action\Traits\FundingProgramIdParameterTrait;

class GetNewApplicationFormAction extends AbstractRemoteFundingAction {

  use FundingCaseTypeIdParameterTrait;

  use FundingProgramIdParameterTrait;

  use NewApplicationFormActionTrait;

  public function __construct() {
    parent::__construct(RemoteFundingCase::getEntityName(), 'getNewApplicationForm');
  }

}
