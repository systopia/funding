<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessPermissions;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Handler\AbstractApplicationProcessTaskHandler;
use CRM_Funding_ExtensionUtil as E;

abstract class AbstractApplicationReviewReworkRequestTaskHandler extends AbstractApplicationProcessTaskHandler {

  private const TASK_TYPE = 'application_review_rework_request';

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function createTasksOnChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): iterable {
    if ($this->isStatusChangedToReworkRequested($applicationProcessBundle, $previousApplicationProcess)) {
      yield $this->createReviewReworkRequestTask($applicationProcessBundle);
    }
  }

  public function createTasksOnNew(ApplicationProcessEntityBundle $applicationProcessBundle): iterable {
    return [];
  }

  public function modifyTask(
    FundingTaskEntity $task,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    if (self::TASK_TYPE !== $task->getType() || $this->isReworkRequested($applicationProcessBundle)) {
      return FALSE;
    }

    $task->setStatusName(ActivityStatusNames::COMPLETED);

    return TRUE;
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to review the rework request.
   */
  protected function getRequiredPermissions(): array {
    return [
      ApplicationProcessPermissions::REVIEW_CALCULATIVE,
      ApplicationProcessPermissions::REVIEW_CONTENT,
    ];
  }

  protected function getTaskSubject(ApplicationProcessEntityBundle $applicationProcessBundle): string {
    return E::ts('Review Rework Request');
  }

  protected function isReworkRequested(ApplicationProcessEntityBundle $applicationProcessBundle): bool {
    return 'rework-requested' === $applicationProcessBundle->getApplicationProcess()->getStatus();
  }

  private function createReviewReworkRequestTask(
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($applicationProcessBundle),
      'affected_identifier' => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::TASK_TYPE,
      'funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
    ]);
  }

  private function isStatusChangedToReworkRequested(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcessBundle->getApplicationProcess()->getStatus() !== $previousApplicationProcess->getStatus()
      && $this->isReworkRequested($applicationProcessBundle);
  }

}
