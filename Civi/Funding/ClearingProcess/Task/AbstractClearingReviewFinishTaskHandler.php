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

namespace Civi\Funding\ClearingProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Handler\ClearingProcessTaskHandlerInterface;
use CRM_Funding_ExtensionUtil as E;

abstract class AbstractClearingReviewFinishTaskHandler implements ClearingProcessTaskHandlerInterface {

  private const TASK_TYPE = 'review_finish';

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function createTasksOnChange(
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): iterable {
    if ($this->isReviewFinishOutstanding($clearingProcessBundle)) {
      yield $this->createReviewFinishTask($clearingProcessBundle);
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
    if (self::TASK_TYPE === $task->getType()) {
      if (!$this->isInReviewStatus($clearingProcessBundle)) {
        $task->setStatusName(ActivityStatusNames::COMPLETED);

        return TRUE;
      }

      if (!$this->isCalculativeAndContentReviewFinished($clearingProcessBundle)) {
        $task->setStatusName(ActivityStatusNames::CANCELLED);

        return TRUE;
      }

      if ($this->areReviewerContactsChanged($clearingProcessBundle->getClearingProcess(), $previousClearingProcess)) {
        $task->setAssigneeContactIds($this->getAssigneeContactIds($clearingProcessBundle->getClearingProcess()));

        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to approve a clearing.
   */
  protected function getRequiredPermissions(): array {
    return [
      ClearingProcessPermissions::REVIEW_CALCULATIVE,
      ClearingProcessPermissions::REVIEW_CONTENT,
    ];
  }

  protected function getTaskSubject(ClearingProcessEntityBundle $clearingProcessBundle): string {
    return E::ts('Finish Clearing Review');
  }

  protected function isReviewFinishOutstanding(
    ClearingProcessEntityBundle $clearingProcessBundle
  ): bool {
    return $this->isInReviewStatus($clearingProcessBundle)
      && $this->isCalculativeAndContentReviewFinished($clearingProcessBundle);
  }

  final protected function isCalculativeAndContentReviewFinished(
    ClearingProcessEntityBundle $clearingProcessBundle
  ): bool {
    return NULL !== $clearingProcessBundle->getClearingProcess()->getIsReviewCalculative()
      && NULL !== $clearingProcessBundle->getClearingProcess()->getIsReviewContent();
  }

  final protected function isInReviewStatus(ClearingProcessEntityBundle $clearingProcessBundle): bool {
    return 'review' === $clearingProcessBundle->getClearingProcess()->getStatus();
  }

  private function areReviewerContactsChanged(
    ClearingProcessEntity $clearingProcess,
    ClearingProcessEntity $previousClearingProcess
  ): bool {
    return $clearingProcess->getReviewerCalculativeContactId()
      !== $previousClearingProcess->getReviewerCalculativeContactId()
      || $clearingProcess->getReviewerContentContactId() !== $previousClearingProcess->getReviewerContentContactId();
  }

  private function createReviewFinishTask(ClearingProcessEntityBundle $clearingProcessBundle): FundingTaskEntity {
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
    $assigneeContactIds = [];
    if (NULL !== $clearingProcess->getReviewerCalculativeContactId()) {
      $assigneeContactIds[] = $clearingProcess->getReviewerCalculativeContactId();
    }
    if (NULL !== $clearingProcess->getReviewerContentContactId()) {
      $assigneeContactIds[] = $clearingProcess->getReviewerContentContactId();
    }

    return $assigneeContactIds;
  }

}
