<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Task;

use Civi\Funding\ApplicationProcess\ApplicationProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\FundingCase\Traits\FundingCaseApproveTaskTrait;
use Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * Should be combined with:
 * @see \Civi\Funding\FundingCase\Task\AbstractFundingCaseApproveTaskModifier
 */
abstract class AbstractFundingCaseApproveTaskCreator implements ApplicationProcessTaskCreatorInterface {

  use FundingCaseApproveTaskTrait;

  /**
   * @throws \CRM_Core_Exception
   */
  public function createTasksOnChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): iterable {
    if ($this->isChangedToEligible($applicationProcessBundle, $previousApplicationProcess)
      && !$this->isFundingCaseApproved($applicationProcessBundle)
      && !$this->existsApplicationWithUndecidedEligibility($applicationProcessBundle)
    ) {
      yield $this->createApproveTask($applicationProcessBundle);
    }
  }

  public function createTasksOnNew(ApplicationProcessEntityBundle $applicationProcessBundle): iterable {
    return [];
  }

  /**
   * @phpstan-return list<string>
   */
  protected function getRequiredPermissions(): array {
    return [
      ApplicationProcessPermissions::REVIEW_CALCULATIVE,
      ApplicationProcessPermissions::REVIEW_CONTENT,
    ];
  }

  protected function getTaskSubject(FundingCaseBundle $fundingCaseBundle): string {
    return E::ts('Approve Funding Case');
  }

  private function isChangedToEligible(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcessBundle->getApplicationProcess()->getIsEligible()
      !== $previousApplicationProcess->getIsEligible()
      && TRUE === $applicationProcessBundle->getApplicationProcess()->getIsEligible();
  }

  private function isFundingCaseApproved(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    return $applicationProcessBundle->getFundingCase()->getAmountApproved() !== NULL;
  }

  private function createApproveTask(FundingCaseBundle $fundingCaseBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($fundingCaseBundle),
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::$taskType,
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);
  }

}
