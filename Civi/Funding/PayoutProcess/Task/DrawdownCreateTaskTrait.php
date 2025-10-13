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

namespace Civi\Funding\PayoutProcess\Task;

use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\Entity\PayoutProcessBundle;
use CRM_Funding_ExtensionUtil as E;

trait DrawdownCreateTaskTrait {

  protected static string $TASK_TYPE = 'drawdown_create';

  final protected function createCreateTask(PayoutProcessBundle $payoutProcessBundle): FundingTaskEntity {
    return FundingTaskEntity::newTask([
      'subject' => $this->getTaskSubject($payoutProcessBundle),
      'affected_identifier' => $payoutProcessBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => ['drawdown_create'],
      'type' => self::$TASK_TYPE,
      'payout_process_id' => $payoutProcessBundle->getPayoutProcess()->getId(),
      'funding_case_id' => $payoutProcessBundle->getFundingCase()->getId(),
    ]);
  }

  protected function getTaskSubject(PayoutProcessBundle $payoutProcessBundle): string {
    return E::ts('Create Drawdown');
  }

}
