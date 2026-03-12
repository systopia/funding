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
use Civi\Funding\FundingCase\Command\TransferContractRecreateCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

class RecreateTransferContractAction extends AbstractAction {

  use IdParameterTrait;

  use ApplicationProcessManagerTrait;

  use FundingCaseManagerTrait;

  private ?TransferContractRecreateHandlerInterface $transferContractRecreateHandler;

  public function __construct(
    ?ApplicationProcessManager $applicationProcessManager = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?TransferContractRecreateHandlerInterface $transferContractRecreateHandler = NULL
  ) {
    parent::__construct(FundingCase::getEntityName(), 'recreateTransferContract');
    $this->_applicationProcessManager = $applicationProcessManager;
    $this->_fundingCaseManager = $fundingCaseManager;
    $this->transferContractRecreateHandler = $transferContractRecreateHandler;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    $fundingCaseBundle = $this->getFundingCaseManager()->getBundle($this->getId());
    Assert::notNull($fundingCaseBundle, E::ts('Funding case with ID "%1" not found', [1 => $this->getId()]));
    $fundingCase = $fundingCaseBundle->getFundingCase();

    $this->getTransferContractRecreateHandler()->handle(new TransferContractRecreateCommand(
      $fundingCaseBundle,
      $this->getApplicationProcessManager()->getStatusListByFundingCaseId($fundingCase->getId()),
    ));

    $result->exchangeArray($fundingCase->toArray());
  }

  private function getTransferContractRecreateHandler(): TransferContractRecreateHandlerInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->transferContractRecreateHandler
      ??= \Civi::service(TransferContractRecreateHandlerInterface::class);
  }

}
