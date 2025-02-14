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

use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\FundingCase\Traits\FundingCaseFinishClearingTaskTrait;
use Civi\Funding\Task\Creator\ClearingProcessTaskCreatorInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * Should be combined with:
 * @see \Civi\Funding\FundingCase\Task\AbstractFundingCaseFinishClearingTaskModifier
 */
abstract class AbstractFundingCaseFinishClearingTaskCreator implements ClearingProcessTaskCreatorInterface {

  use FundingCaseFinishClearingTaskTrait;

  /**
   * @throws \CRM_Core_Exception
   */
  public function createTasksOnChange(
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): iterable {
    if ($this->isChangedToAcceptedOrRejected($clearingProcessBundle, $previousClearingProcess)
      && $this->isFinishClearingPossible($clearingProcessBundle)
    ) {
      yield $this->createFinishClearingTask($clearingProcessBundle);
    }
  }

  /**
   * @codeCoverageIgnore
   */
  public function createTasksOnNew(ClearingProcessEntityBundle $clearingProcessBundle): iterable {
    return [];
  }

  /**
   * @phpstan-return list<string>
   */
  protected function getRequiredPermissions(): array {
    return [
      ClearingProcessPermissions::REVIEW_CALCULATIVE,
      ClearingProcessPermissions::REVIEW_CONTENT,
    ];
  }

  protected function getTaskSubject(FundingCaseBundle $fundingCaseBundle): string {
    return E::ts('Finish Funding Case Clearing');
  }

  private function createFinishClearingTask(FundingCaseBundle $fundingCaseBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($fundingCaseBundle),
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::$taskType,
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);
  }

  private function isChangedToAcceptedOrRejected(
    ClearingProcessEntityBundle $clearingProcessBundle,
    ClearingProcessEntity $previousClearingProcess
  ): bool {
    return $clearingProcessBundle->getClearingProcess()->getStatus() !== $previousClearingProcess->getStatus()
      && in_array(
        $clearingProcessBundle->getClearingProcess()->getStatus(),
        ['accepted', 'rejected'],
        TRUE
      );
  }

}
