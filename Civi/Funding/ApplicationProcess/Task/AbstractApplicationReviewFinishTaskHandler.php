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

namespace Civi\Funding\ApplicationProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Handler\ApplicationProcessTaskHandlerInterface;
use CRM_Funding_ExtensionUtil as E;

abstract class AbstractApplicationReviewFinishTaskHandler implements ApplicationProcessTaskHandlerInterface {

  private const TASK_TYPE = 'review_finish';

  private ApplicationProcessActionStatusInfoContainer $infoContainer;

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function __construct(ApplicationProcessActionStatusInfoContainer $infoContainer) {
    $this->infoContainer = $infoContainer;
  }

  public function createTasksOnChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): iterable {
    if ($this->isReviewFinishOutstanding($applicationProcessBundle)) {
      yield $this->createReviewFinishTask($applicationProcessBundle);
    }
  }

  /**
   * @codeCoverageIgnore
   */
  public function createTasksOnNew(ApplicationProcessEntityBundle $applicationProcessBundle): iterable {
    return [];
  }

  public function modifyTask(
    FundingTaskEntity $task,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    if (self::TASK_TYPE === $task->getType()) {
      if (!$this->isInReviewStatus($applicationProcessBundle)) {
        $task->setStatusName(ActivityStatusNames::COMPLETED);

        return TRUE;
      }

      if (!$this->isCalculativeAndContentReviewFinished($applicationProcessBundle)) {
        $task->setStatusName(ActivityStatusNames::CANCELLED);

        return TRUE;
      }

      $applicationProcess = $applicationProcessBundle->getApplicationProcess();
      if ($this->areReviewerContactsChanged($applicationProcess, $previousApplicationProcess)) {
        $task->setAssigneeContactIds($this->getAssigneeContactIds($applicationProcess));

        return TRUE;
      }
    }

    return FALSE;
  }

  final protected function getInfo(
    FundingCaseTypeEntity $fundingCaseType
  ): ApplicationProcessActionStatusInfoInterface {
    return $this->infoContainer->get($fundingCaseType->getName());
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to approve an application.
   */
  protected function getRequiredPermissions(): array {
    return [
      ApplicationProcessPermissions::REVIEW_CALCULATIVE,
      ApplicationProcessPermissions::REVIEW_CONTENT,
    ];
  }

  protected function getTaskSubject(ApplicationProcessEntityBundle $applicationProcessBundle): string {
    return E::ts('Finish Application Review');
  }

  protected function isReviewFinishOutstanding(
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): bool {
    return $this->isInReviewStatus($applicationProcessBundle)
      && $this->isCalculativeAndContentReviewFinished($applicationProcessBundle);
  }

  final protected function isCalculativeAndContentReviewFinished(
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): bool {
    return NULL !== $applicationProcessBundle->getApplicationProcess()->getIsReviewCalculative()
      && NULL !== $applicationProcessBundle->getApplicationProcess()->getIsReviewContent();
  }

  protected function isInReviewStatus(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    return $this->getInfo($applicationProcessBundle->getFundingCaseType())
      ->isReviewStatus($applicationProcessBundle->getApplicationProcess()->getStatus());
  }

  private function areReviewerContactsChanged(
    ApplicationProcessEntity $applicationProcess,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcess->getReviewerCalculativeContactId()
      !== $previousApplicationProcess->getReviewerCalculativeContactId()
      || $applicationProcess->getReviewerContentContactId()
      !== $previousApplicationProcess->getReviewerContentContactId();
  }

  private function createReviewFinishTask(ApplicationProcessEntityBundle $applicationProcessBundle): FundingTaskEntity {
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
    $assigneeContactIds = [];
    if (NULL !== $applicationProcess->getReviewerCalculativeContactId()) {
      $assigneeContactIds[] = $applicationProcess->getReviewerCalculativeContactId();
    }
    if (NULL !== $applicationProcess->getReviewerContentContactId()) {
      $assigneeContactIds[] = $applicationProcess->getReviewerContentContactId();
    }

    return $assigneeContactIds;
  }

}
