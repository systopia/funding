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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Api4\Action\FundingClearingProcess\ApplyActionMultipleAction;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\Funding\ClearingProcess\Command\ClearingActionApplyCommand;
use Civi\Funding\ClearingProcess\Handler\ClearingActionApplyHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

final class ApplyActionMultipleActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingClearingProcess';

  private ClearingActionApplyHandlerInterface $actionApplyHandler;

  private ClearingProcessBundleLoader $clearingProcessBundleLoader;

  public function __construct(
    ClearingActionApplyHandlerInterface $actionApplyHandler,
    ClearingProcessBundleLoader $clearingProcessBundleLoader
  ) {
    $this->actionApplyHandler = $actionApplyHandler;
    $this->clearingProcessBundleLoader = $clearingProcessBundleLoader;
  }

  /**
   * Applies an action to multiple clearing processes. The action must be
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
    $clearingProcessBundle = $this->clearingProcessBundleLoader->get($id);
    if (NULL === $clearingProcessBundle) {
      throw new UnauthorizedException(sprintf('Clearing process with ID %d not found.', $id));
    }

    $this->actionApplyHandler->handle(new ClearingActionApplyCommand(
      $clearingProcessBundle,
      $action
    ));

    $clearingProcess = $clearingProcessBundle->getClearingProcess();

    return [
      'status' => $clearingProcess->getStatus(),
      'is_review_calculative' => $clearingProcess->getIsReviewCalculative(),
      'is_review_content' => $clearingProcess->getIsReviewContent(),
    ];
  }

}
