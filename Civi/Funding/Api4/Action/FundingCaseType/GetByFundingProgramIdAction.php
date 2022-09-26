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

namespace Civi\Funding\Api4\Action\FundingCaseType;

use Civi\Api4\FundingCaseType;
use Civi\Api4\FundingCaseTypeProgram;
use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @method $this setFundingProgramId(int $fundingProgramId)
 */
final class GetByFundingProgramIdAction extends AbstractAction {

  /**
   * @var int
   * @required
   */
  protected ?int $fundingProgramId = NULL;

  private Api4Interface $_api4;

  public function __construct(Api4Interface $api4) {
    parent::__construct(FundingCaseType::_getEntityName(), 'getByFundingProgramId');
    $this->_api4 = $api4;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    if ($this->hasFundingProgramAccess()) {
      $action = FundingCaseType::get()->setDebug($this->getDebug())
        ->addJoin(
          FundingCaseTypeProgram::_getEntityName() . ' AS cp', 'INNER', NULL,
          ['cp.funding_case_type_id', '=', 'id']
        )->addWhere('cp.funding_program_id', '=', $this->fundingProgramId);
      $action->_run($result);
      if ($this->getDebug()) {
        $this->_debugOutput['get'] = $action->_debugOutput;
      }
    }
  }

  /**
   * @throws \API_Exception
   */
  private function hasFundingProgramAccess(): bool {
    $action = FundingProgram::get()
      ->setDebug($this->getDebug())
      ->addSelect('id')
      ->addWhere('id', '=', $this->fundingProgramId);

    $result = $this->_api4->executeAction($action);
    if ($this->getDebug()) {
      $this->_debugOutput['hasFundingProgramAccess'] = $result->debug;
    }

    return 1 === $result->count();
  }

}
