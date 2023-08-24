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

namespace Civi\Funding\Api4\Action\FundingCase;

use Civi\Api4\FundingCase;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\FundingCase\Command\FundingCasePossibleActionsGetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

class GetPossibleActionsAction extends AbstractAction {

  use IdParameterTrait;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingCasePossibleActionsGetHandlerInterface $possibleActionsGetHandler;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingCasePossibleActionsGetHandlerInterface $possibleActionsGetHandler
  ) {
    parent::__construct(FundingCase::_getEntityName(), 'getPossibleActions');
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->possibleActionsGetHandler = $possibleActionsGetHandler;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $fundingCase = $this->fundingCaseManager->get($this->getId());
    Assert::notNull($fundingCase, E::ts('Funding case with ID "%1" not found', [1 => $this->getId()]));
    $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);

    $actions = $this->possibleActionsGetHandler->handle(
      new FundingCasePossibleActionsGetCommand($fundingCase, $fundingCaseType)
    );

    $result->exchangeArray($actions);
  }

}
