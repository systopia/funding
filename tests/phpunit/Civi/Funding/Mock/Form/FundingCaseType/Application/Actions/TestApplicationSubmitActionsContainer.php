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

namespace Civi\Funding\Mock\Form\FundingCaseType\Application\Actions;

use Civi\Funding\ApplicationProcess\ActionsContainer\AbstractApplicationSubmitActionsContainerDecorator;
use Civi\Funding\ApplicationProcess\ActionsContainer\ReworkPossibleApplicationSubmitActionsContainerFactory;
use Civi\Funding\Mock\Form\FundingCaseType\Traits\TestSupportedFundingCaseTypesTrait;

final class TestApplicationSubmitActionsContainer extends AbstractApplicationSubmitActionsContainerDecorator {

  use TestSupportedFundingCaseTypesTrait;

  public function __construct() {
    parent::__construct(ReworkPossibleApplicationSubmitActionsContainerFactory::create());
  }

}
