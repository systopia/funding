<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingCase\GetSearchTasksAction;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;

final class GetSearchTasksActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingCase';

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(PayoutProcessManager $payoutProcessManager) {
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @phpstan-return array<int, array<string, array<string, mixed>>>
   *   Mapping of funding case ID to mapping of task name to task.
   *
   * @throws \CRM_Core_Exception
   */
  public function getSearchTasks(GetSearchTasksAction $action): array {
    $tasks = [];

    foreach ($action->getIds() as $id) {
      $tasks[$id] = $this->getSearchTasksByFundingCaseId($id);
    }

    return $tasks;
  }

  /**
   * @return array<string, array<string, mixed>>
   *   Mapping of task name to task definition.
   *
   * @throws \CRM_Core_Exception
   */
  private function getSearchTasksByFundingCaseId(int $fundingCaseId): array {
    $tasks = [];

    if ($this->isDrawdownCreatePossible($fundingCaseId)) {
      $tasks['createDrawdown'] = [
        'module' => 'crmFunding',
        'title' => E::ts('Create drawdown'),
        'uiDialog' => [
          'templateUrl' => '~/crmFunding/case/searchTask/fundingCaseSearchTaskCreateDrawdown.template.html',
        ],
      ];
    }

    return $tasks;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function isDrawdownCreatePossible(int $fundingCaseId): bool {
    $payoutProcessBundle = $this->payoutProcessManager->getLastBundleByFundingCaseId($fundingCaseId);

    return NULL !== $payoutProcessBundle
      && $payoutProcessBundle->getFundingCase()->hasPermission(FundingCasePermissions::REVIEW_DRAWDOWN_CREATE)
      && $this->payoutProcessManager->getAmountAvailable($payoutProcessBundle->getPayoutProcess()) > 0;
  }

}
