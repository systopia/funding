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

namespace Civi\Funding\ApplicationProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Handler\ApplicationProcessTaskHandlerInterface;
use CRM_Funding_ExtensionUtil as E;

abstract class AbstractApplicationReviewContentTaskHandler implements ApplicationProcessTaskHandlerInterface {

  private const TASK_TYPE = 'review_content';

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function createTasksOnChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): iterable {
    if ($this->isStatusChangedToReviewable($applicationProcessBundle, $previousApplicationProcess)
      && NULL === $applicationProcessBundle->getApplicationProcess()->getIsReviewContent()
    ) {
      yield $this->createReviewTask($applicationProcessBundle);
    }
  }

  public function createTasksOnNew(ApplicationProcessEntityBundle $applicationProcessBundle): iterable {
    if ($this->isInReviewableStatus($applicationProcessBundle)
      && NULL === $applicationProcessBundle->getApplicationProcess()->getIsReviewContent()
    ) {
      yield $this->createReviewTask($applicationProcessBundle);
    }
  }

  public function modifyTask(
    FundingTaskEntity $task,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    if (self::TASK_TYPE !== $task->getType()) {
      return FALSE;
    }

    $changed = FALSE;
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    if ($applicationProcess->getReviewerContentContactId() !==
      $previousApplicationProcess->getReviewerContentContactId()
    ) {
      $task->setAssigneeContactIds($this->getAssigneeContactIds($applicationProcessBundle->getApplicationProcess()));
      $changed = TRUE;
    }

    if (NULL !== $applicationProcess->getIsReviewContent()) {
      $task->setStatusName(ActivityStatusNames::COMPLETED);
      $changed = TRUE;
    }
    elseif ($this->isStatusChangedToNonReviewable($applicationProcessBundle, $previousApplicationProcess)) {
      $task->setStatusName(ActivityStatusNames::CANCELLED);
      $changed = TRUE;
    }

    return $changed;
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to review an application
   *   content-wise.
   */
  protected function getRequiredPermissions(): array {
    return [ApplicationProcessPermissions::REVIEW_CONTENT];
  }

  protected function getTaskSubject(ApplicationProcessEntityBundle $applicationProcessBundle): string {
    return E::ts('Review Application (content)');
  }

  protected function isInReviewableStatus(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    return !$applicationProcessBundle->getApplicationProcess()->getIsInWork()
      && NULL === $applicationProcessBundle->getApplicationProcess()->getIsEligible();
  }

  private function createReviewTask(ApplicationProcessEntityBundle $applicationProcessBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($applicationProcessBundle),
      'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::TASK_TYPE,
      'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
      'assignee_contact_ids' => $this->getAssigneeContactIds($applicationProcessBundle->getApplicationProcess()),
    ]);
  }

  /**
   * @phpstan-return list<int>
   */
  private function getAssigneeContactIds(ApplicationProcessEntity $applicationProcess): array {
    return NULL === $applicationProcess->getReviewerContentContactId()
      ? [] : [$applicationProcess->getReviewerContentContactId()];
  }

  private function isStatusChangedToReviewable(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcessBundle->getApplicationProcess()->getStatus() !== $previousApplicationProcess->getStatus()
      && $this->isInReviewableStatus($applicationProcessBundle);
  }

  private function isStatusChangedToNonReviewable(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcessBundle->getApplicationProcess()->getStatus() !== $previousApplicationProcess->getStatus()
      && !$this->isInReviewableStatus($applicationProcessBundle);
  }

}
