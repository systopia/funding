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

namespace Civi\Funding\FundingCaseType\Api4\ActionHandler;

use Civi\Api4\FundingCaseType;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Remote\FundingCaseType\GetByFundingProgramIdAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

final class RemoteGetByFundingProgramIdActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCaseType';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getByFundingProgramId(GetByFundingProgramIdAction $action): Result {
    $action = FundingCaseType::getByFundingProgramId(FALSE)
      ->setFundingProgramId($action->getFundingProgramId());

    return $this->api4->executeAction($action);
  }

}
