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
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\FundingCase\Command\FundingCaseApproveCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface;
use Civi\Funding\FundingCase\TransferContractRouter;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @method $this setAmount(float $amount)
 */
class ApproveAction extends AbstractAction {

  use IdParameterTrait;

  /**
   * @var mixed
   * @required
   * @phpstan-ignore-next-line CiviCRM (v5.59) does not know float/double in @var.
   */
  protected ?float $amount = NULL;

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseApproveHandlerInterface $approveHandler;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private TransferContractRouter $transferContractRouter;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseApproveHandlerInterface $approveHandler,
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    TransferContractRouter $transferContractRouter
  ) {
    parent::__construct(FundingCase::_getEntityName(), 'approve');
    $this->applicationProcessManager = $applicationProcessManager;
    $this->approveHandler = $approveHandler;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->transferContractRouter = $transferContractRouter;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    Assert::greaterThan($this->amount, 0);
    $fundingCase = $this->fundingCaseManager->get($this->getId());
    Assert::notNull($fundingCase, E::ts('Funding case with ID "%1" not found', [1 => $this->getId()]));
    $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);
    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram, sprintf(
      'No permission to access funding program with ID "%d"',
      $fundingCase->getFundingProgramId()
    ));

    $command = new FundingCaseApproveCommand(
      $fundingCase,
      $this->amount,
      $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId()),
      $fundingCaseType,
      $fundingProgram,
    );
    $this->approveHandler->handle($command);

    $fundingCase->setTransferContractUri($this->transferContractRouter->generate($fundingCase->getId()));
    $result->exchangeArray($fundingCase->toArray());
  }

}
