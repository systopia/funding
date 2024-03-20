<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Actions;

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;

final class DefaultFundingCaseActionsDeterminer extends FundingCaseActionsDeterminer {

  private ApplicationProcessActionStatusInfoInterface $statusInfo;

  private const STATUS_PERMISSIONS_ACTION_MAP = [
    'open' => [
      'review_calculative' => ['approve'],
      'review_content' => ['approve'],
    ],
    'ongoing' => [
      'review_calculative' => ['recreate-transfer-contract', FundingCaseActions::UPDATE_AMOUNT_APPROVED],
      'review_content' => ['recreate-transfer-contract', FundingCaseActions::UPDATE_AMOUNT_APPROVED],
    ],
  ];

  public function __construct(ApplicationProcessActionStatusInfoInterface $statusInfo) {
    parent::__construct(self::STATUS_PERMISSIONS_ACTION_MAP);
    $this->statusInfo = $statusInfo;
  }

  public function getActions(string $status, array $applicationProcessStatusList, array $permissions): array {
    $actions = parent::getActions(
      $status,
      $applicationProcessStatusList,
      $permissions
    );

    $posApprove = array_search('approve', $actions, TRUE);
    if (FALSE !== $posApprove && !$this->isApprovePossible($applicationProcessStatusList)) {
      unset($actions[$posApprove]);
      $actions = array_values($actions);
    }

    return $actions;
  }

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *
   * @return bool
   *   TRUE If there's at least one eligible application and the eligibility of
   *   all applications is decided.
   */
  private function isApprovePossible(array $applicationProcessStatusList): bool {
    $eligibleCount = 0;
    foreach ($applicationProcessStatusList as $applicationProcessStatus) {
      $eligible = $this->statusInfo->isEligibleStatus($applicationProcessStatus->getStatus());
      if (NULL === $eligible) {
        return FALSE;
      }

      if ($eligible) {
        ++$eligibleCount;
      }
    }

    return $eligibleCount > 0;
  }

}
