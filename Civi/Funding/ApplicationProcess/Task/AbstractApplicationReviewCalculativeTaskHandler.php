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
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Handler\ApplicationProcessTaskHandlerInterface;
use CRM_Funding_ExtensionUtil as E;

abstract class AbstractApplicationReviewCalculativeTaskHandler implements ApplicationProcessTaskHandlerInterface {

  private const TASK_TYPE = 'review_calculative';

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
    if ($this->isStatusChangedToReview($applicationProcessBundle, $previousApplicationProcess)
      && NULL === $applicationProcessBundle->getApplicationProcess()->getIsReviewCalculative()
    ) {
      yield $this->createReviewTask($applicationProcessBundle);
    }
  }

  public function createTasksOnNew(ApplicationProcessEntityBundle $applicationProcessBundle): iterable {
    if ($this->isInReviewStatus($applicationProcessBundle)
      && NULL === $applicationProcessBundle->getApplicationProcess()->getIsReviewCalculative()
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
    if ($applicationProcess->getReviewerCalculativeContactId() !==
      $previousApplicationProcess->getReviewerCalculativeContactId()
    ) {
      $task->setAssigneeContactIds($this->getAssigneeContactIds($applicationProcessBundle->getApplicationProcess()));
      $changed = TRUE;
    }

    if (NULL !== $applicationProcess->getIsReviewCalculative()) {
      $task->setStatusName(ActivityStatusNames::COMPLETED);
      $changed = TRUE;
    }
    elseif ($this->isStatusChangedToNonReview($applicationProcessBundle, $previousApplicationProcess)) {
      $task->setStatusName(ActivityStatusNames::CANCELLED);
      $changed = TRUE;
    }

    return $changed;
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to review an application
   *   calculative.
   */
  protected function getRequiredPermissions(): array {
    return [ApplicationProcessPermissions::REVIEW_CALCULATIVE];
  }

  protected function getTaskSubject(ApplicationProcessEntityBundle $applicationProcessBundle): string {
    return E::ts('Review Funding Application (calculative)');
  }

  protected function isInReviewStatus(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    return $this->getInfo($applicationProcessBundle->getFundingCaseType())
      ->isReviewStatus($applicationProcessBundle->getApplicationProcess()->getStatus());
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
    return NULL === $applicationProcess->getReviewerCalculativeContactId()
      ? [] : [$applicationProcess->getReviewerCalculativeContactId()];
  }

  private function getInfo(FundingCaseTypeEntity $fundingCaseType): ApplicationProcessActionStatusInfoInterface {
    return $this->infoContainer->get($fundingCaseType->getName());
  }

  private function isStatusChangedToReview(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcessBundle->getApplicationProcess()->getStatus() !== $previousApplicationProcess->getStatus()
      && $this->isInReviewStatus($applicationProcessBundle);
  }

  private function isStatusChangedToNonReview(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcessBundle->getApplicationProcess()->getStatus() !== $previousApplicationProcess->getStatus()
      && !$this->isInReviewStatus($applicationProcessBundle);
  }

}
