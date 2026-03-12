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

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingCase\FinishClearingAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\FundingCase\Command\FundingCaseFinishClearingCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFinishClearingHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class FinishClearingActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingCase';

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseFinishClearingHandlerInterface $finishClearingHandler;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseFinishClearingHandlerInterface $finishClearingHandler,
    FundingCaseManager $fundingCaseManager,
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->finishClearingHandler = $finishClearingHandler;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * @phpstan-return array<string, mixed>
   */
  public function finishClearing(FinishClearingAction $action): array {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($action->getId());
    Assert::notNull($fundingCaseBundle, E::ts('Funding case with ID "%1" not found', [1 => $action->getId()]));
    $fundingCase = $fundingCaseBundle->getFundingCase();

    $command = new FundingCaseFinishClearingCommand(
      $fundingCaseBundle,
      $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId()),
    );
    $this->finishClearingHandler->handle($command);

    return $fundingCase->toArray();
  }

}
