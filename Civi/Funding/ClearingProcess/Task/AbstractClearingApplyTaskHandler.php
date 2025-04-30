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

abstract class AbstractClearingApplyTaskHandler implements ClearingProcessTaskHandlerInterface {

  private const TASK_TYPE = 'apply';

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function createTasksOnChange(
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): iterable {
    if ($this->isStatusChangedToAppliable($clearingProcessBundle, $previousClearingProcess)) {
      yield $this->createApplyTask($clearingProcessBundle);
    }
  }

  /**
   * @codeCoverageIgnore
   */
  public function createTasksOnNew(ClearingProcessEntityBundle $clearingProcessBundle): iterable {
    return [];
  }

  public function modifyTask(
    FundingTaskEntity $task,
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): bool {
    if (self::TASK_TYPE !== $task->getType() || $this->isInAppliableStatus($clearingProcessBundle)) {
      return FALSE;
    }

    $task->setStatusName(ActivityStatusNames::COMPLETED);

    return TRUE;
  }

  protected function getTaskSubject(ClearingProcessEntityBundle $clearingProcessBundle): string {
    return E::ts('Complete and Apply Clearing');
  }

  private function createApplyTask(ClearingProcessEntityBundle $clearingProcessBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($clearingProcessBundle),
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::TASK_TYPE,
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
    ]);
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to apply a clearing.
   */
  private function getRequiredPermissions(): array {
    return [ClearingProcessPermissions::CLEARING_APPLY];
  }

  private function isInAppliableStatus(ClearingProcessEntityBundle $clearingProcessBundle): bool {
    return in_array($clearingProcessBundle->getClearingProcess()->getStatus(), ['draft', 'rework'], TRUE);
  }

  private function isStatusChangedToAppliable(
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): bool {
    return $clearingProcessBundle->getClearingProcess()->getStatus() !== $previousClearingProcess->getStatus()
      && $this->isInAppliableStatus($clearingProcessBundle);
  }

}
