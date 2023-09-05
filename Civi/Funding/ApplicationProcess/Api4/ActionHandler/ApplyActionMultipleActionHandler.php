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
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationActionApplyCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationAllowedActionsGetCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationAllowedActionsGetHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;

final class ApplyActionMultipleActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingApplicationProcess';

  private ApplicationActionApplyHandlerInterface $actionApplyHandler;

  private ApplicationAllowedActionsGetHandlerInterface $allowedActionsGetHandler;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private RequestContextInterface $requestContext;

  public function __construct(
    ApplicationActionApplyHandlerInterface $actionApplyHandler,
    ApplicationAllowedActionsGetHandlerInterface $allowedActionsGetHandler,
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    RequestContextInterface $requestContext
  ) {
    $this->actionApplyHandler = $actionApplyHandler;
    $this->allowedActionsGetHandler = $allowedActionsGetHandler;
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->requestContext = $requestContext;
  }

  /**
   * Applies an action to multiple application processes. The action must be
   * applicable without form data.
   *
   * @phpstan-return array<int, array{status: string, is_review_calculative: bool|null, is_review_content: bool|null}>
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  public function applyActionMultiple(ApplyActionMultipleAction $action): array {
    $newStatusList = [];

    foreach ($action->getIds() as $id) {
      $newStatusList[$id] = $this->applyActionById($action->getAction(), $id);
    }

    return $newStatusList;
  }

  /**
   * @phpstan-return array{status: string, is_review_calculative: bool|null, is_review_content: bool|null}
   *
   * @throws \Civi\API\Exception\UnauthorizedException
   * @throws \CRM_Core_Exception
   */
  private function applyActionById(string $action, int $id): array {
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($id);
    if (NULL === $applicationProcessBundle) {
      throw new UnauthorizedException(E::ts('Application process with ID %1 not found.', [1 => $id]));
    }

    $applicationProcessStatusList = $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle);
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

    $this->actionApplyHandler->handle(new ApplicationActionApplyCommand(
      $this->requestContext->getContactId(),
      $action,
      $applicationProcessBundle,
      NULL
    ));

    $applicationProcess = $applicationProcessBundle->getApplicationProcess();

    return [
      'status' => $applicationProcess->getStatus(),
      'is_review_calculative' => $applicationProcess->getIsReviewCalculative(),
      'is_review_content' => $applicationProcess->getIsReviewContent(),
    ];
  }

}
