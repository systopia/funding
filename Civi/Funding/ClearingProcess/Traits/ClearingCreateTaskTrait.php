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

namespace Civi\Funding\ClearingProcess\Traits;

use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use CRM_Funding_ExtensionUtil as E;

trait ClearingCreateTaskTrait {

  protected static string $taskType = 'create';

  final protected function createCreateTask(ClearingProcessEntityBundle $clearingProcessBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($clearingProcessBundle),
      'affected_identifier' => $clearingProcessBundle->getApplicationProcess()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::$taskType,
      'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      'application_process_id' => $clearingProcessBundle->getApplicationProcess()->getId(),
      'clearing_process_id' => $clearingProcessBundle->getClearingProcess()->getId(),
    ]);
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to create a clearing.
   */
  final protected function getRequiredPermissions(): array {
    return [ClearingProcessPermissions::CLEARING_APPLY, ClearingProcessPermissions::CLEARING_MODIFY];
  }

  protected function getTaskSubject(ClearingProcessEntityBundle $clearingProcessBundle): string {
    return E::ts('Create Clearing');
  }

  final protected function isClearingStarted(ClearingProcessEntity $clearingProcess): bool {
    return 'not-started' !== $clearingProcess->getStatus();
  }

}
