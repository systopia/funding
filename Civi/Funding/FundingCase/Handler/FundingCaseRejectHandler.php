<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseRejectCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;

final class FundingCaseRejectHandler implements FundingCaseRejectHandlerInterface {

  private FundingCaseActionsDeterminerInterface $actionsDeterminer;

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationProcessStatusDeterminerInterface $applicationProcessStatusDeterminer;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseStatusDeterminerInterface $statusDeterminer;

  public function __construct(
    FundingCaseActionsDeterminerInterface $actionsDeterminer,
    ApplicationProcessManager $applicationProcessManager,
    ApplicationProcessStatusDeterminerInterface $applicationProcessStatusDeterminer,
    FundingCaseManager $fundingCaseManager,
    FundingCaseStatusDeterminerInterface $statusDeterminer
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->applicationProcessStatusDeterminer = $applicationProcessStatusDeterminer;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->statusDeterminer = $statusDeterminer;
  }

  /**
   * @throws \Civi\Funding\Exception\FundingException
   * @throws \CRM_Core_Exception
   */
  public function handle(FundingCaseRejectCommand $command): void {
    $this->assertAuthorized($command);

    $fundingCase = $command->getFundingCase();
    foreach ($this->applicationProcessManager->getByFundingCaseId($fundingCase->getId()) as $applicationProcess) {
      if (NULL === $applicationProcess->getIsEligible()) {
        $applicationProcess->setFullStatus(
          $this->applicationProcessStatusDeterminer->getStatus($applicationProcess->getFullStatus(), 'reject')
        );
        $this->applicationProcessManager->update(new ApplicationProcessEntityBundle(
          $applicationProcess,
          $command->getFundingCase(),
          $command->getFundingCaseType(),
          $command->getFundingProgram()
        ));
      }
    }

    $newStatus = $this->statusDeterminer->getStatus(
      $fundingCase->getStatus(),
      FundingCaseActions::REJECT
    );
    if ($newStatus !== $fundingCase->getStatus()) {
      $fundingCase->setStatus($newStatus);
      $this->fundingCaseManager->update($fundingCase);
    }
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function assertAuthorized(FundingCaseRejectCommand $command): void {
    if (!$this->actionsDeterminer->isActionAllowed(
      FundingCaseActions::REJECT,
      $command->getFundingCase()->getStatus(),
      $command->getApplicationProcessStatusList(),
      $command->getFundingCase()->getPermissions(),
    )) {
      throw new UnauthorizedException(sprintf(
        'Rejecting funding case "%s" is not allowed.',
        $command->getFundingCase()->getIdentifier()
      ));
    }
  }

}
