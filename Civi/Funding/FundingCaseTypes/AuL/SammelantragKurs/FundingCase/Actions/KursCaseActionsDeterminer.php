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

namespace Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\FundingCase\Actions;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FundingCase\Actions\AbstractFundingCaseActionsDeterminerDecorator;
use Civi\Funding\FundingCase\Actions\DefaultFundingCaseActionsDeterminer;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\Actions\KursApplicationActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Application\Actions\KursApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\KursMetaData;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\Traits\KursSupportedFundingCaseTypesTrait;

final class KursCaseActionsDeterminer extends AbstractFundingCaseActionsDeterminerDecorator {

  use KursSupportedFundingCaseTypesTrait;

  private const EXTRA_STATUS_PERMISSIONS_ACTION_MAP = [
    'ongoing' => [
      'review_calculative' => [FundingCaseActions::UPDATE_AMOUNT_APPROVED],
      'review_content' => [FundingCaseActions::UPDATE_AMOUNT_APPROVED],
    ],
  ];

  private KursApplicationActionsDeterminer $applicationActionsDeterminer;

  private ApplicationProcessManager $applicationProcessManager;

  public function __construct(
    KursApplicationActionsDeterminer $applicationActionsDeterminer,
    KursApplicationStatusDeterminer $applicationStatusDeterminer,
    ApplicationProcessManager $applicationProcessManager,
    ClearingProcessManager $clearingProcessManager,
    KursMetaData $metaData
  ) {
    parent::__construct(new DefaultFundingCaseActionsDeterminer(
      $applicationStatusDeterminer, $clearingProcessManager, $metaData
    ));
    $this->applicationActionsDeterminer = $applicationActionsDeterminer;
    $this->applicationProcessManager = $applicationProcessManager;
  }

  /**
   * @inheritDoc
   */
  public function getActions(FundingCaseBundle $fundingCaseBundle, array $applicationProcessStatusList): array {
    $actions = [];

    foreach ($applicationProcessStatusList as $id => $applicationProcessStatus) {
      $curStatusList = $applicationProcessStatusList;
      unset($curStatusList[$id]);
      if ($this->applicationActionsDeterminer->isActionAllowed(
        'apply',
        // @phpstan-ignore argument.type
        $this->applicationProcessManager->getBundle($id),
        $curStatusList
      )) {
        $actions[] = 'apply';
        break;
      }
      elseif ($this->applicationActionsDeterminer->isActionAllowed(
        'review',
        // @phpstan-ignore argument.type
        $this->applicationProcessManager->getBundle($id),
        $curStatusList
      )) {
        $actions[] = 'review';
        break;
      }
    }

    $status = $fundingCaseBundle->getFundingCase()->getStatus();
    $permissions = $fundingCaseBundle->getFundingCase()->getPermissions();
    if ($this->isActionAllowedForAllApplications('delete', $applicationProcessStatusList)) {
      $actions[] = 'delete';
    }
    elseif ($this->isActionAllowedForAllApplications('withdraw', $applicationProcessStatusList)) {
      $actions[] = 'withdraw';
    }

    foreach ($permissions as $permission) {
      $actions = array_merge($actions, self::EXTRA_STATUS_PERMISSIONS_ACTION_MAP[$status][$permission] ?? []);
    }

    return array_values(array_unique(array_merge(
      $actions,
      parent::getActions($fundingCaseBundle, $applicationProcessStatusList)
    )));
  }

  /**
   * @inheritDoc
   */
  public function getInitialActions(FundingCaseTypeEntity $fundingCaseType, array $permissions): array {
    if (in_array('application_create', $permissions, TRUE)) {
      return ['save'];
    }

    return [];
  }

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   */
  private function isActionAllowedForAllApplications(
    string $action,
    array $applicationProcessStatusList
  ): bool {
    foreach ($applicationProcessStatusList as $id => $applicationProcessStatus) {
      $curStatusList = $applicationProcessStatusList;
      unset($curStatusList[$id]);
      if (!$this->applicationActionsDeterminer->isActionAllowed(
        $action,
        // @phpstan-ignore argument.type
        $this->applicationProcessManager->getBundle($id),
        $curStatusList
      )) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
