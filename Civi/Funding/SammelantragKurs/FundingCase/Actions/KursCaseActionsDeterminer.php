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

namespace Civi\Funding\SammelantragKurs\FundingCase\Actions;

use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\FundingCase\Actions\AbstractFundingCaseActionsDeterminerDecorator;
use Civi\Funding\FundingCase\Actions\DefaultFundingCaseActionsDeterminer;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationActionsDeterminer;
use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationActionStatusInfo;
use Civi\Funding\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursCaseActionsDeterminer extends AbstractFundingCaseActionsDeterminerDecorator {

  use KursSupportedFundingCaseTypesTrait;

  private const EXTRA_STATUS_PERMISSIONS_ACTION_MAP = [
    'ongoing' => [
      'review_calculative' => [FundingCaseActions::UPDATE_AMOUNT_APPROVED],
      'review_content' => [FundingCaseActions::UPDATE_AMOUNT_APPROVED],
    ],
  ];

  private KursApplicationActionsDeterminer $applicationActionsDeterminer;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  public function __construct(
    KursApplicationActionsDeterminer $applicationActionsDeterminer,
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ClearingProcessManager $clearingProcessManager,
    KursApplicationActionStatusInfo $statusInfo
  ) {
    parent::__construct(new DefaultFundingCaseActionsDeterminer($clearingProcessManager, $statusInfo));
    $this->applicationActionsDeterminer = $applicationActionsDeterminer;
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
  }

  /**
   * @inheritDoc
   */
  public function getActions(
    string $status,
    array $applicationProcessStatusList,
    array $permissions
  ): array {
    $actions = [];

    foreach ($applicationProcessStatusList as $id => $applicationProcessStatus) {
      $curStatusList = $applicationProcessStatusList;
      unset($curStatusList[$id]);
      if ($this->applicationActionsDeterminer->isActionAllowed(
        'apply',
        // @phpstan-ignore argument.type
        $this->applicationProcessBundleLoader->get($id),
        $curStatusList
      )) {
        $actions[] = 'apply';
        break;
      }
      elseif ($this->applicationActionsDeterminer->isActionAllowed(
        'review',
        // @phpstan-ignore argument.type
        $this->applicationProcessBundleLoader->get($id),
        $curStatusList
      )) {
        $actions[] = 'review';
        break;
      }
    }

    if ($this->isActionAllowedForAllApplications('delete', $applicationProcessStatusList, $permissions)) {
      $actions[] = 'delete';
    }

    foreach ($permissions as $permission) {
      $actions = array_merge($actions, self::EXTRA_STATUS_PERMISSIONS_ACTION_MAP[$status][$permission] ?? []);
    }

    return array_values(array_unique(array_merge(
      $actions,
      parent::getActions($status, $applicationProcessStatusList, $permissions)
    )));
  }

  /**
   * @inheritDoc
   */
  public function getInitialActions(array $permissions): array {
    if (in_array('application_create', $permissions, TRUE)) {
      return ['save'];
    }

    return [];
  }

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   * @phpstan-param array<string> $permissions
   */
  private function isActionAllowedForAllApplications(
    string $action,
    array $applicationProcessStatusList,
    array $permissions
  ): bool {
    foreach ($applicationProcessStatusList as $id => $applicationProcessStatus) {
      $curStatusList = $applicationProcessStatusList;
      unset($curStatusList[$id]);
      if (!$this->applicationActionsDeterminer->isActionAllowed(
        $action,
        // @phpstan-ignore argument.type
        $this->applicationProcessBundleLoader->get($id),
        $curStatusList
      )) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
