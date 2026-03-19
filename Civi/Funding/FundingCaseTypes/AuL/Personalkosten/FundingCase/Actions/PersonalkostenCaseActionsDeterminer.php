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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\FundingCase\Actions;

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\FundingCase\Actions\AbstractFundingCaseActionsDeterminerDecorator;
use Civi\Funding\FundingCase\Actions\DefaultFundingCaseActionsDeterminer;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Actions\PersonalkostenApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\PersonalkostenMetaData;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;

final class PersonalkostenCaseActionsDeterminer extends AbstractFundingCaseActionsDeterminerDecorator {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  private PersonalkostenMetaData $metaData;

  public function __construct(
    PersonalkostenApplicationStatusDeterminer $applicationStatusDeterminer,
    ClearingProcessManager $clearingProcessManager,
    PersonalkostenMetaData $metaData
  ) {
    $this->metaData = $metaData;
    parent::__construct(new DefaultFundingCaseActionsDeterminer(
      $applicationStatusDeterminer, $clearingProcessManager, $metaData
    ));
  }

  public function getActions(FundingCaseBundle $fundingCaseBundle, array $applicationProcessStatusList): array {
    $actions = parent::getActions($fundingCaseBundle, $applicationProcessStatusList);

    $fundingCase = $fundingCaseBundle->getFundingCase();
    if (
      $fundingCase->hasPermission(FundingCasePermissions::AUTO_UPDATE_AMOUNT_APPROVED)
      && $fundingCase->getStatus() === FundingCaseStatus::ONGOING
      && !in_array(FundingCaseActions::UPDATE_AMOUNT_APPROVED, $actions, TRUE)
      && $this->isAllEligibilityDecided($applicationProcessStatusList)
    ) {
      $actions[] = FundingCaseActions::UPDATE_AMOUNT_APPROVED;
    }

    return $actions;
  }

  /**
   * @param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *
   * @return bool
   *   TRUE If the eligibility of all applications is decided.
   */
  private function isAllEligibilityDecided(array $applicationProcessStatusList): bool {
    foreach ($applicationProcessStatusList as $applicationProcessStatus) {
      $eligible = $this->metaData->getApplicationProcessStatus($applicationProcessStatus->getStatus())?->isEligible();
      if (NULL === $eligible) {
        return FALSE;
      }
    }

    return TRUE;
  }

}
