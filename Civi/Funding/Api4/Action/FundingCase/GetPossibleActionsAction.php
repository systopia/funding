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
use Civi\Funding\Api4\Action\Traits\ApplicationProcessManagerTrait;
use Civi\Funding\Api4\Action\Traits\FundingCaseManagerTrait;
use Civi\Funding\Api4\Action\Traits\FundingCaseTypeManagerTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\FundingCase\Command\FundingCasePossibleActionsGetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

class GetPossibleActionsAction extends AbstractAction {

  use IdParameterTrait;

  use ApplicationProcessManagerTrait;

  use FundingCaseManagerTrait;

  use FundingCaseTypeManagerTrait;

  private ?FundingCasePossibleActionsGetHandlerInterface $possibleActionsGetHandler;

  public function __construct(
    ?ApplicationProcessManager $applicationProcessManager = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?FundingCaseTypeManager $fundingCaseTypeManager = NULL,
    ?FundingCasePossibleActionsGetHandlerInterface $possibleActionsGetHandler = NULL
  ) {
    parent::__construct(FundingCase::getEntityName(), 'getPossibleActions');
    $this->_applicationProcessManager = $applicationProcessManager;
    $this->_fundingCaseManager = $fundingCaseManager;
    $this->_fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->possibleActionsGetHandler = $possibleActionsGetHandler;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $fundingCase = $this->getFundingCaseManager()->get($this->getId());
    Assert::notNull($fundingCase, E::ts('Funding case with ID "%1" not found', [1 => $this->getId()]));
    $fundingCaseType = $this->getFundingCaseTypeManager()->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);

    $actions = $this->getPossibleActionsGetHandler()->handle(
      new FundingCasePossibleActionsGetCommand(
        $fundingCase,
        $this->getApplicationProcessManager()->getStatusListByFundingCaseId($fundingCase->getId()),
        $fundingCaseType,
      )
    );

    $result->exchangeArray($actions);
  }

  private function getPossibleActionsGetHandler(): FundingCasePossibleActionsGetHandlerInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->possibleActionsGetHandler ??= \Civi::service(FundingCasePossibleActionsGetHandlerInterface::class);
  }

}
