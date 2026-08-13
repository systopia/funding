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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\FundingApplicationProcess\ApplyActionMultipleAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\BatchActionHandler\MoveToNewFundingCaseHandler;
use Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationAllowedActionsGetCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationAllowedActionsGetHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;

final class ApplyActionMultipleActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingApplicationProcess';

  public function __construct(
    private readonly ApplicationActionApplyHandlerInterface $actionApplyHandler,
    private readonly ApplicationAllowedActionsGetHandlerInterface $allowedActionsGetHandler,
    private readonly ApplicationProcessManager $applicationProcessManager,
    private readonly MoveToNewFundingCaseHandler $moveToNewFundingCaseApplier,
  ) {}

  /**
   * Applies an action to multiple application processes. The action must be
   * applicable without form data.
   *
   * @return array<int, array{status: string, is_review_calculative: bool|null, is_review_content: bool|null, ...}>
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function applyActionMultiple(ApplyActionMultipleAction $action): array {
    $applicationProcessBundles = $this->getApplicationProcessBundles($action->getIds(), $action->getAction());

    // Note: Should there be more than one action that has to be handled
    // differently, we should use a service container containing the appliers
    // (tagged services).
    if (MoveToNewFundingCaseHandler::ACTION === $action->getAction()) {
      return $this->moveToNewFundingCaseApplier->handle($applicationProcessBundles);
    }

    $newStatusList = [];
    foreach ($applicationProcessBundles as $applicationProcessBundle) {
      $newStatusList[$applicationProcessBundle->getApplicationProcess()->getId()]
        = $this->applyAction($action->getAction(), $applicationProcessBundle);
    }

    return $newStatusList;
  }

  /**
   * @phpstan-return array{status: string, is_review_calculative: bool|null, is_review_content: bool|null}
   */
  private function applyAction(string $action, ApplicationProcessEntityBundle $applicationProcessBundle): array {
    $this->actionApplyHandler->handle(new ApplicationActionApplyCommand(
      $action, $applicationProcessBundle, NULL
    ));

    $applicationProcess = $applicationProcessBundle->getApplicationProcess();

    return [
      'status' => $applicationProcess->getStatus(),
      'is_review_calculative' => $applicationProcess->getIsReviewCalculative(),
      'is_review_content' => $applicationProcess->getIsReviewContent(),
    ];
  }

  /**
   * @param list<int> $ids
   *
   * @phpstan-return iterable<ApplicationProcessEntityBundle>
   *
   * @throws \CRM_Core_Exception
   */
  private function getApplicationProcessBundles(array $ids, string $action): iterable {
    foreach ($ids as $id) {
      $applicationProcessBundle = $this->applicationProcessManager->getBundle($id);
      if (NULL === $applicationProcessBundle) {
        throw new UnauthorizedException(E::ts('Application process with ID %1 not found.', [1 => $id]));
      }

      $applicationProcessStatusList = $this->applicationProcessManager->getStatusList($applicationProcessBundle);
      $allowedActions = $this->allowedActionsGetHandler->handle(new ApplicationAllowedActionsGetCommand(
        $applicationProcessBundle,
        $applicationProcessStatusList,
      ));

      if (!isset($allowedActions[$action])) {
        throw new UnauthorizedException(E::ts('Performing action %1 on application process %2 is not allowed.', [
          1 => $action,
          2 => $applicationProcessBundle->getApplicationProcess()->getIdentifier(),
        ]));
      }

      yield $applicationProcessBundle;
    }
  }

}
