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

namespace Civi\Funding\FundingCase\Task;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\FundingCase\Traits\CombinedApplicationCaseTaskTrait;
use Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * Creates a funding case task if an application process of a combined
 * application is in a status in which it can be applied.
 *
 * Should be combined with:
 * @see \Civi\Funding\FundingCase\Task\AbstractCombinedApplicationApplyTaskModifier
 */
abstract class AbstractCombinedApplicationApplyTaskCreator implements ApplicationProcessTaskCreatorInterface {

  use CombinedApplicationCaseTaskTrait;

  public function createTasksOnChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): iterable {
    if ($this->isStatusChangedToAppliable($applicationProcessBundle, $previousApplicationProcess)) {
      yield $this->createApplyTask($applicationProcessBundle);
    }
  }

  public function createTasksOnNew(ApplicationProcessEntityBundle $applicationProcessBundle): iterable {
    if ($this->isInAppliableStatus($applicationProcessBundle)) {
      yield $this->createApplyTask($applicationProcessBundle);
    }
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to apply an application.
   */
  protected function getRequiredPermissions(): array {
    return ['application_apply'];
  }

  protected function getTaskSubject(FundingCaseBundle $fundingCaseBundle): string {
    return E::ts('Complete and Apply Application');
  }

  private function createApplyTask(FundingCaseBundle $fundingCaseBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($fundingCaseBundle),
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::$taskType,
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);
  }

  protected function isInAppliableStatus(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    return $applicationProcessBundle->getApplicationProcess()->getIsInWork();
  }

  private function isStatusChangedToAppliable(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcessBundle->getApplicationProcess()->getStatus() !== $previousApplicationProcess->getStatus()
      && $this->isInAppliableStatus($applicationProcessBundle);
  }

}
