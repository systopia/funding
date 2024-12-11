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

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Handler\ClearingProcessTaskHandlerInterface;
use CRM_Funding_ExtensionUtil as E;

abstract class AbstractClearingReviewCalculativeTaskHandler implements ClearingProcessTaskHandlerInterface {

  private const TASK_TYPE = 'review_calculative';

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function createTasksOnChange(
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): iterable {
    if ($this->isStatusChangedToReviewable($clearingProcessBundle, $previousClearingProcess)
      && NULL === $clearingProcessBundle->getClearingProcess()->getIsReviewCalculative()
    ) {
      yield $this->createReviewTask($clearingProcessBundle);
    }
  }

  public function createTasksOnNew(ClearingProcessEntityBundle $clearingProcessBundle): iterable {
    return [];
  }

  public function modifyTask(
    FundingTaskEntity $task,
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): bool {
    if (self::TASK_TYPE !== $task->getType()) {
      return FALSE;
    }

    $changed = FALSE;
    $clearingProcess = $clearingProcessBundle->getClearingProcess();
    if ($clearingProcess->getReviewerCalculativeContactId() !==
      $previousClearingProcess->getReviewerCalculativeContactId()
    ) {
      $task->setAssigneeContactIds($this->getAssigneeContactIds($clearingProcessBundle->getClearingProcess()));
      $changed = TRUE;
    }

    if (NULL !== $clearingProcess->getIsReviewCalculative()) {
      $task->setStatusName(ActivityStatusNames::COMPLETED);
      $changed = TRUE;
    }
    elseif ($this->isStatusChangedToNonReviewable($clearingProcessBundle, $previousClearingProcess)) {
      $task->setStatusName(ActivityStatusNames::CANCELLED);
      $changed = TRUE;
    }

    return $changed;
  }

  protected function getTaskSubject(ClearingProcessEntityBundle $clearingProcessBundle): string {
    return E::ts('Review Clearing (calculative)');
  }

  private function createReviewTask(ClearingProcessEntityBundle $clearingProcessBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($clearingProcessBundle),
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::TASK_TYPE,
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
      'assignee_contact_ids' => $this->getAssigneeContactIds($clearingProcessBundle->getClearingProcess()),
    ]);
  }

  /**
   * @phpstan-return list<int>
   */
  private function getAssigneeContactIds(ClearingProcessEntity $clearingProcess): array {
    return NULL === $clearingProcess->getReviewerCalculativeContactId()
      ? [] : [$clearingProcess->getReviewerCalculativeContactId()];
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to review an clearing
   *   calculative.
   */
  private function getRequiredPermissions(): array {
    return [ClearingProcessPermissions::REVIEW_CALCULATIVE];
  }

  private function isInReviewableStatus(ClearingProcessEntityBundle $clearingProcessBundle): bool {
    return in_array($clearingProcessBundle->getClearingProcess()->getStatus(), ['review-requested', 'review'], TRUE);
  }

  private function isStatusChangedToReviewable(
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): bool {
    return $clearingProcessBundle->getClearingProcess()->getStatus() !== $previousClearingProcess->getStatus()
      && $this->isInReviewableStatus($clearingProcessBundle);
  }

  private function isStatusChangedToNonReviewable(
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): bool {
    return $clearingProcessBundle->getClearingProcess()->getStatus() !== $previousClearingProcess->getStatus()
      && !$this->isInReviewableStatus($clearingProcessBundle);
  }

}
