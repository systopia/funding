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

namespace Civi\Funding\Api4\Action\FundingCaseTypeProgram;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;

/**
 * @method int getFundingCaseTypeId()
 * @method int getFundingProgramId()
 */
class GetRelationAction extends AbstractAction {

  /**
   * @var int
   * @required
   */
  protected ?int $fundingCaseTypeId = NULL;

  /**
   * @var int
   * @required
   */
  protected ?int $fundingProgramId = NULL;

  private AbstractGetAction $getAction;

  public function __construct(AbstractGetAction $getAction) {
    parent::__construct($getAction->getEntityName(), 'getRelation');
    $this->getAction = $getAction;
  }

  /**
   * @inheritDoc
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $this->getAction
      ->setCheckPermissions($this->getCheckPermissions())
      ->setDebug($this->getDebug())
      ->addWhere('funding_case_type_id', '=', $this->fundingCaseTypeId)
      ->addWhere('funding_program_id', '=', $this->fundingProgramId);
    $this->getAction->_run($result);
    $this->_debugOutput = $this->getAction->_debugOutput;
  }

  public function setFundingCaseTypeId(int $fundingCaseTypeId): self {
    $this->fundingCaseTypeId = $fundingCaseTypeId;

    return $this;
  }

  public function setFundingProgramId(int $fundingProgramId): self {
    $this->fundingProgramId = $fundingProgramId;

    return $this;
  }

}
