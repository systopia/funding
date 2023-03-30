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

namespace Civi\Funding\FundingCase\Handler;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\Command\FundingCasePossibleActionsGetCommand;
use Civi\Funding\FundingCase\FundingCaseActionsDeterminerInterface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class FundingCasePossibleActionsGetHandler implements FundingCasePossibleActionsGetHandlerInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseActionsDeterminerInterface $actionsDeterminer;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseActionsDeterminerInterface $actionsDeterminer
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->actionsDeterminer = $actionsDeterminer;
  }

  /**
   * @inheritDoc
   */
  public function handle(FundingCasePossibleActionsGetCommand $command): array {
    $fundingCase = $command->getFundingCase();
    $actions = $this->actionsDeterminer->getActions($fundingCase->getStatus(), $fundingCase->getPermissions());
    $posApprove = array_search('approve', $actions, TRUE);
    if (FALSE !== $posApprove && !$this->isApprovePossible($fundingCase)) {
      unset($actions[$posApprove]);
      $actions = array_values($actions);
    }

    return $actions;
  }

  private function isApprovePossible(FundingCaseEntity $fundingCase): bool {
    return $this->applicationProcessManager->countBy(CompositeCondition::fromFieldValuePairs([
      'funding_case_id' => $fundingCase->getId(),
      'is_eligible' => NULL,
    ])) === 0
    && $this->applicationProcessManager->countBy(CompositeCondition::fromFieldValuePairs([
      'funding_case_id' => $fundingCase->getId(),
      'is_eligible' => TRUE,
    ])) > 0;
  }

}
