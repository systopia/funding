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

namespace Civi\Funding\FundingCase\Handler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\FundingCase\Command\TransferContractRecreateCommand;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\TransferContract\TransferContractCreator;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class TransferContractRecreateHandler implements TransferContractRecreateHandlerInterface {

  private FundingCaseActionsDeterminerInterface $actionsDeterminer;

  private TransferContractCreator $transferContractCreator;

  public function __construct(
    FundingCaseActionsDeterminerInterface $actionsDeterminer,
    TransferContractCreator $transferContractCreator
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->transferContractCreator = $transferContractCreator;
  }

  /**
   * @throws \Civi\Funding\Exception\FundingException
   * @throws \CRM_Core_Exception
   */
  public function handle(TransferContractRecreateCommand $command): void {
    $fundingCase = $command->getFundingCase();
    $this->assertAuthorized($command);
    Assert::notNull($fundingCase->getAmountApproved(), 'Funding case has no approved amount.');

    $this->transferContractCreator->createTransferContract(
      $fundingCase,
      $command->getFundingCaseType(),
      $command->getFundingProgram(),
    );
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function assertAuthorized(TransferContractRecreateCommand $command): void {
    if (!$this->actionsDeterminer->isActionAllowed(
      'recreate-transfer-contract',
      $command->getFundingCase()->getStatus(),
      $command->getApplicationProcessStatusList(),
      $command->getFundingCase()->getPermissions(),
    )) {
      throw new UnauthorizedException(E::ts('Permission to recreate transfer contract is missing.'));
    }
  }

}
