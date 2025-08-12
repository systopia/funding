<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

use Civi\Funding\Api4\Action\FundingCase\RejectAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\FundingCase\Command\FundingCaseRejectCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseRejectHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class RejectActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingCase';

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseRejectHandlerInterface $rejectHandler;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingCaseManager $fundingCaseManager,
    FundingCaseRejectHandlerInterface $rejectHandler,
  ) {
    $this->applicationProcessManager = $applicationProcessManager;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->rejectHandler = $rejectHandler;
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * @phpstan-return array<string, mixed>
   */
  public function reject(RejectAction $action): array {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($action->getId());
    Assert::notNull($fundingCaseBundle, E::ts('Funding case with ID "%1" not found', [1 => $action->getId()]));

    $command = new FundingCaseRejectCommand(
      $fundingCaseBundle,
      $this->applicationProcessManager->getStatusListByFundingCaseId($action->getId()),
    );
    $this->rejectHandler->handle($command);

    return $fundingCaseBundle->getFundingCase()->toArray();
  }

}
