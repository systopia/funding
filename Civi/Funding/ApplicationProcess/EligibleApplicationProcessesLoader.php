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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Exception\FundingException;
use CRM_Funding_ExtensionUtil as E;

class EligibleApplicationProcessesLoader {

  private ApplicationProcessManager $applicationProcessManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
  }

  /**
   * @phpstan-return array<\Civi\Funding\Entity\ApplicationProcessEntity>
   *
   * @throws \CRM_Core_Exception
   * @throws \Civi\Funding\Exception\FundingException
   *   If there is an application that is neither eligible nor in a final
   *   status.
   */
  public function getEligibleProcessesForContract(FundingCaseEntity $fundingCase): array {
    $eligibleApplicationProcesses = [];
    foreach ($this->applicationProcessManager->getByFundingCaseId($fundingCase->getId()) as $applicationProcess) {
      if (NULL === $applicationProcess->getIsEligible()) {
        throw new FundingException(E::ts(
          'The eligibility of application "%1" is not decided (current status: %2).',
          [
            1 => $applicationProcess->getIdentifier(),
            2 => $applicationProcess->getStatus(),
          ]
        ));
      }

      if ($applicationProcess->getIsEligible()) {
        $eligibleApplicationProcesses[] = $applicationProcess;
      }
    }

    return $eligibleApplicationProcesses;
  }

}
