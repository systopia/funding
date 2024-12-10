<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Upgrade;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\FundingCase\Command\FundingCaseUpdateAmountApprovedCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class Upgrader0009 implements UpgraderInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private FundingCaseUpdateAmountApprovedHandlerInterface $updateAmountApprovedHandler;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    FundingCaseUpdateAmountApprovedHandlerInterface $updateAmountApprovedHandler
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->updateAmountApprovedHandler = $updateAmountApprovedHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function execute(\Log $log): void {
    $log->info('Set amount approved to 0.0 for withdrawn funding cases');
    $withdrawnFundingCases = $this->fundingCaseManager->getBy(CompositeCondition::new('AND',
      Comparison::new('status', '=', FundingCaseStatus::WITHDRAWN),
      Comparison::new('amount_approved', '>', 0),
    ));
    foreach ($withdrawnFundingCases as $fundingCase) {
      $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
      assert(NULL !== $fundingCaseType);
      $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
      assert(NULL !== $fundingProgram);

      $this->updateAmountApprovedHandler->handle((new FundingCaseUpdateAmountApprovedCommand(
        $fundingCase,
        0.0,
        $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId()),
        $fundingCaseType,
        $fundingProgram
      ))->setAuthorized(TRUE));
    }
  }

}
