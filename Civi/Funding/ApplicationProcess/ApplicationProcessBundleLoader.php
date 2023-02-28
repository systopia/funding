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

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Webmozart\Assert\Assert;

class ApplicationProcessBundleLoader {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $applicationProcessId): ?ApplicationProcessEntityBundle {
    $applicationProcess = $this->applicationProcessManager->get($applicationProcessId);

    return NULL === $applicationProcess ? NULL : $this->createFromApplicationProcess($applicationProcess);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFirstByFundingCaseId(int $fundingCaseId): ?ApplicationProcessEntityBundle {
    $applicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCaseId);

    return NULL === $applicationProcess ? NULL : $this->createFromApplicationProcess($applicationProcess);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createFromApplicationProcess(
    ApplicationProcessEntity $applicationProcess
  ): ApplicationProcessEntityBundle {
    $fundingCase = $this->fundingCaseManager->get($applicationProcess->getFundingCaseId());
    Assert::notNull($fundingCase);

    $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);

    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram, sprintf(
      'No permission to access funding program with ID "%d"',
      $fundingCase->getFundingProgramId()
    ));

    return new ApplicationProcessEntityBundle($applicationProcess, $fundingCase, $fundingCaseType, $fundingProgram);
  }

}
