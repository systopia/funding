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

namespace Civi\Funding\FundingCase\StatusDeterminer;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class DefaultFundingCaseStatusDeterminer implements FundingCaseStatusDeterminerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseTypeMetaDataProviderInterface $metaDataProvider;

  /**
   * @inheritDoc
   */
  public static function getSupportedFundingCaseTypes(): array {
    return [];
  }

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseTypeMetaDataProviderInterface $metaDataProvider
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->metaDataProvider = $metaDataProvider;
  }

  public function getStatus(string $currentStatus, string $action): string {
    switch ($action) {
      case FundingCaseActions::APPROVE:
        return FundingCaseStatus::ONGOING;

      case FundingCaseActions::FINISH_CLEARING:
        return FundingCaseStatus::CLEARED;

      default:
        return $currentStatus;
    }
  }

  public function getStatusOnApplicationProcessStatusChange(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    string $previousStatus
  ): string {
    $ineligibleStatusList = array_keys($this->getFinalIneligibleStatuses(
      $applicationProcessBundle->getFundingCaseType()
    ));

    if (in_array(
      $applicationProcessBundle->getApplicationProcess()->getStatus(),
      $ineligibleStatusList,
      TRUE
    ) && 0 === $this->applicationProcessManager->countBy(
        CompositeCondition::new('AND',
          Comparison::new('funding_case_id', '=', $applicationProcessBundle->getFundingCase()->getId()),
          Comparison::new('status', 'NOT IN', $ineligibleStatusList),
        ),
      )) {
      // Application process status has changed to an ineligible status and
      // there is no other application process that is eligible or with an
      // undecided eligibility.
      return $applicationProcessBundle->getApplicationProcess()->getIsWithdrawn()
        ? FundingCaseStatus::WITHDRAWN
        : FundingCaseStatus::REJECTED;
    }

    return $applicationProcessBundle->getFundingCase()->getStatus();
  }

  /**
   * @phpstan-return array<string, \Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus>
   */
  private function getFinalIneligibleStatuses(FundingCaseTypeEntity $fundingCaseType): array {
    $statuses = $this->metaDataProvider->get($fundingCaseType->getName())->getApplicationProcessStatuses();
    $ineligibleStatuses = [];
    foreach ($statuses as $name => $status) {
      if ($status->isFinal() && FALSE === $status->isEligible()) {
        $ineligibleStatuses[$name] = $status;
      }
    }

    return $ineligibleStatuses;
  }

}
