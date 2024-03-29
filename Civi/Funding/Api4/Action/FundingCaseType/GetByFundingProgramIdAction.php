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
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\FundingProgramIdParameterTrait;
use Civi\RemoteTools\Api4\Api4Interface;
use Psr\Log\LoggerInterface;

final class GetByFundingProgramIdAction extends AbstractAction {

  use FundingProgramIdParameterTrait;

  private Api4Interface $api4;

  private LoggerInterface $logger;

  public function __construct(Api4Interface $api4, LoggerInterface $logger) {
    parent::__construct(FundingCaseType::getEntityName(), 'getByFundingProgramId');
    $this->api4 = $api4;
    $this->logger = $logger;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    if (!$this->fundingProgramExists()) {
      $this->logger->debug(sprintf('A funding program with id "%d" does not exist', $this->fundingProgramId));
    }
    elseif (!$this->hasFundingProgramAccess()) {
      $this->logger->debug(sprintf('Contact has no access to funding program with id "%d"', $this->fundingProgramId));
    }
    else {
      $action = FundingCaseType::get($this->getCheckPermissions())
        ->setDebug($this->getDebug())
        ->addJoin(
          FundingCaseTypeProgram::getEntityName() . ' AS cp', 'INNER', NULL,
          ['cp.funding_case_type_id', '=', 'id']
        )->addWhere('cp.funding_program_id', '=', $this->getFundingProgramId());
      $action->_run($result);
      if ($this->getDebug()) {
        $this->_debugOutput['get'] = $action->_debugOutput;
      }
    }
  }

  private function fundingProgramExists(): bool {
    $action = (new DAOGetAction(FundingProgram::getEntityName(), 'get'))
      ->setCheckPermissions($this->getCheckPermissions())
      ->selectRowCount()
      ->addWhere('id', '=', $this->getFundingProgramId());

    $result = $this->api4->executeAction($action);
    if ($this->getDebug()) {
      $this->_debugOutput['fundingProgramExists'] = $result->debug;
    }

    return 1 === $result->countMatched();
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function hasFundingProgramAccess(): bool {
    $action = FundingProgram::get($this->getCheckPermissions())
      ->setDebug($this->getDebug())
      ->addSelect('id')
      ->addWhere('id', '=', $this->getFundingProgramId());

    $result = $this->api4->executeAction($action);
    if ($this->getDebug()) {
      $this->_debugOutput['hasFundingProgramAccess'] = $result->debug;
    }

    return 1 === $result->count();
  }

}
