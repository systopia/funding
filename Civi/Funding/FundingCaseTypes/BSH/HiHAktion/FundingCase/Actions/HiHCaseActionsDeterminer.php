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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase\Actions;

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\FundingCase\Actions\FundingCaseActions as Actions;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminer;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\FundingCase\FundingCaseStatus as Status;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\HiHMetaData;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;

final class HiHCaseActionsDeterminer extends FundingCaseActionsDeterminer {

  use HiHSupportedFundingCaseTypesTrait;

  private const STATUS_PERMISSIONS_ACTION_MAP = [
    Status::OPEN => [
      'review_application' => [Actions::SET_RECIPIENT_CONTACT, Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CONTENT => [Actions::SET_NOTIFICATION_CONTACTS],
      'review_drawdown' => [Actions::SET_NOTIFICATION_CONTACTS],
      'bsh_admin' => [Actions::APPROVE],
    ],
    Status::ONGOING => [
      'review_application' => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CONTENT => [Actions::SET_NOTIFICATION_CONTACTS],
      'review_drawdown' => [Actions::SET_NOTIFICATION_CONTACTS],
      FundingCasePermissions::REVIEW_FINISH => [Actions::FINISH_CLEARING, Actions::SET_NOTIFICATION_CONTACTS],
      'bsh_admin' => [Actions::RECREATE_TRANSFER_CONTRACT],
    ],
    Status::CLEARED => [
      'review_application' => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CONTENT => [Actions::SET_NOTIFICATION_CONTACTS],
      'review_drawdown' => [Actions::SET_NOTIFICATION_CONTACTS],
      FundingCasePermissions::REVIEW_FINISH => [Actions::SET_NOTIFICATION_CONTACTS],
    ],
    Status::REJECTED => [
      'review_application' => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CONTENT => [Actions::SET_NOTIFICATION_CONTACTS],
      'review_drawdown' => [Actions::SET_NOTIFICATION_CONTACTS],
      FundingCasePermissions::REVIEW_FINISH => [Actions::SET_NOTIFICATION_CONTACTS],
    ],
    Status::WITHDRAWN => [
      'review_application' => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CALCULATIVE => [Actions::SET_NOTIFICATION_CONTACTS],
      ClearingProcessPermissions::REVIEW_CONTENT => [Actions::SET_NOTIFICATION_CONTACTS],
      'review_drawdown' => [Actions::SET_NOTIFICATION_CONTACTS],
      FundingCasePermissions::REVIEW_FINISH => [Actions::SET_NOTIFICATION_CONTACTS],
    ],
  ];

  private ClearingProcessManager $clearingProcessManager;

  private HiHMetaData $metaData;

  public function __construct(
    ClearingProcessManager $clearingProcessManager,
    HiHMetaData $metaData
  ) {
    parent::__construct(self::STATUS_PERMISSIONS_ACTION_MAP);
    $this->clearingProcessManager = $clearingProcessManager;
    $this->metaData = $metaData;
  }

  public function getActions(string $status, array $applicationProcessStatusList, array $permissions): array {
    $actions = parent::getActions(
      $status,
      $applicationProcessStatusList,
      $permissions
    );

    $posApprove = array_search(Actions::APPROVE, $actions, TRUE);
    if (FALSE !== $posApprove && !$this->isApprovePossible($applicationProcessStatusList)) {
      unset($actions[$posApprove]);
      $actions = array_values($actions);
    }

    $posFinishClearing = array_search(Actions::FINISH_CLEARING, $actions, TRUE);
    if (FALSE !== $posFinishClearing && !$this->isFinishClearingPossible($applicationProcessStatusList)) {
      unset($actions[$posFinishClearing]);
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
      $eligible = $this->metaData->getApplicationProcessStatus($applicationProcessStatus->getStatus())?->isEligible();
      if (NULL === $eligible) {
        return FALSE;
      }

      if ($eligible) {
        ++$eligibleCount;
      }
    }

    return $eligibleCount > 0;
  }

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   */
  private function isFinishClearingPossible(array $applicationProcessStatusList): bool {
    foreach ($applicationProcessStatusList as $applicationProcessId => $applicationProcessStatus) {
      // Eligibility of all applications has to be decided.
      $eligible = $this->metaData->getApplicationProcessStatus($applicationProcessStatus->getStatus())?->isEligible();
      if (NULL === $eligible) {
        return FALSE;
      }

      if (TRUE === $eligible) {
        // There has to be a clearing process for every eligible application that is either accepted or rejected.
        $clearingProcess = $this->clearingProcessManager->getByApplicationProcessId($applicationProcessId);
        if (NULL === $clearingProcess || !in_array($clearingProcess->getStatus(), ['accepted', 'rejected'], TRUE)) {
          return FALSE;
        }
      }
    }

    return TRUE;
  }

}
