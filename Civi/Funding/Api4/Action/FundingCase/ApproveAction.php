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
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\FundingCase\Command\FundingCaseApproveCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface;
use Civi\Funding\FundingCase\TransferContractRouter;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @method float getAmount()
 * @method $this setAmount(float $amount)
 */
class ApproveAction extends AbstractAction {

  use IdParameterTrait;

  use ApplicationProcessManagerTrait;

  use FundingCaseManagerTrait;

  /**
   * @var mixed CiviCRM (v5.59) does not know float/double in @var.
   * @required
   * @phpstan-ignore property.phpDocType
   */
  protected ?float $amount = NULL;

  private ?FundingCaseApproveHandlerInterface $approveHandler;

  private ?TransferContractRouter $transferContractRouter;

  public function __construct(
    ?ApplicationProcessManager $applicationProcessManager = NULL,
    ?FundingCaseApproveHandlerInterface $approveHandler = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?TransferContractRouter $transferContractRouter = NULL
  ) {
    parent::__construct(FundingCase::getEntityName(), 'approve');
    $this->_applicationProcessManager = $applicationProcessManager;
    $this->approveHandler = $approveHandler;
    $this->_fundingCaseManager = $fundingCaseManager;
    $this->transferContractRouter = $transferContractRouter;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    Assert::greaterThan($this->amount, 0);
    $fundingCaseBundle = $this->getFundingCaseManager()->getBundle($this->getId());
    Assert::notNull($fundingCaseBundle, E::ts('Funding case with ID "%1" not found', [1 => $this->getId()]));
    $fundingCase = $fundingCaseBundle->getFundingCase();

    $command = new FundingCaseApproveCommand(
      $fundingCaseBundle,
      $this->amount,
      $this->getApplicationProcessManager()->getStatusListByFundingCaseId($fundingCase->getId()),
    );
    $this->getApproveHandler()->handle($command);

    $fundingCase->setTransferContractUri($this->getTransferContractRouter()->generate($fundingCase->getId()));
    $result->exchangeArray($fundingCase->toArray());
  }

  private function getApproveHandler(): FundingCaseApproveHandlerInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->approveHandler ??= \Civi::service(FundingCaseApproveHandlerInterface::class);
  }

  private function getTransferContractRouter(): TransferContractRouter {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->transferContractRouter ??= \Civi::service(TransferContractRouter::class);
  }

}
