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

namespace Civi\Funding\PayoutProcess\Task;

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\Entity\DrawdownBundle;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Task\Handler\DrawdownTaskHandlerInterface;
use CRM_Funding_ExtensionUtil as E;

abstract class AbstractDrawdownReviewTaskHandler implements DrawdownTaskHandlerInterface {

  private const TASK_TYPE = 'review';

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  public function createTasksOnChange(
    DrawdownBundle $drawdownBundle,
    DrawdownEntity $previousDrawdown
  ): iterable {
    if ($this->isStatusChangedToNew($drawdownBundle, $previousDrawdown)) {
      yield $this->createReviewTask($drawdownBundle);
    }
  }

  public function createTasksOnNew(DrawdownBundle $drawdownBundle): iterable {
    if ($this->isInNewStatus($drawdownBundle)) {
      yield $this->createReviewTask($drawdownBundle);
    }
  }

  public function modifyTask(
    FundingTaskEntity $task,
    DrawdownBundle $drawdownBundle,
    DrawdownEntity $previousDrawdown
  ): bool {
    if (self::TASK_TYPE !== $task->getType() || $this->isInNewStatus($drawdownBundle)) {
      return FALSE;
    }

    $task->setStatusName(ActivityStatusNames::COMPLETED);

    return TRUE;
  }

  /**
   * @phpstan-return non-empty-list<string>
   *   One of the returned permissions is required to apply an drawdown.
   */
  protected function getRequiredPermissions(): array {
    return ['review_drawdown'];
  }

  protected function getTaskSubject(DrawdownBundle $drawdownBundle): string {
    return E::ts('Review drawdown');
  }

  protected function isInNewStatus(DrawdownBundle $drawdownBundle): bool {
    return 'new' === $drawdownBundle->getDrawdown()->getStatus();
  }

  private function createReviewTask(DrawdownBundle $drawdownBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($drawdownBundle),
      'affected_identifier' => $drawdownBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => $this->getRequiredPermissions(),
      'type' => self::TASK_TYPE,
      'funding_case_id' => $drawdownBundle->getFundingCase()->getId(),
      'payout_process_id' => $drawdownBundle->getPayoutProcess()->getId(),
      'drawdown_id' => $drawdownBundle->getDrawdown()->getId(),
    ]);
  }

  private function isStatusChangedToNew(
    DrawdownBundle $drawdownBundle,
    DrawdownEntity $previousDrawdown
  ): bool {
    return $drawdownBundle->getDrawdown()->getStatus() !== $previousDrawdown->getStatus()
      && $this->isInNewStatus($drawdownBundle);
  }

}
