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

namespace Civi\Funding\Api4\ActionHandler;

use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * This action handler can be used in cases when there exists a corresponding
 * non-remote action, i.e. an action where the only difference.
 */
abstract class AbstractRemoteFundingActionHandler implements ActionHandlerInterface {

  public function __construct(
    private readonly Api4Interface $api4
  ) {}

  protected function execute(AbstractRemoteFundingAction $action): Result {
    return $this->api4->execute(
      $this->getEntityName($action),
      $this->getActionName($action),
      $this->getParams($action)
    );
  }

  /**
   * @return string The non-remote action name.
   */
  protected function getActionName(AbstractRemoteFundingAction $action): string {
    return $action->getActionName();
  }

  /**
   * @return string The non-remote entity name.
   */
  protected function getEntityName(AbstractRemoteFundingAction $action): string {
    $entityName = $action->getEntityName();
    if (str_starts_with($entityName, 'Remote')) {
      return substr($entityName, 6);
    }

    throw new \InvalidArgumentException(sprintf('Expected entity name "%s" to start with "Remote"', $entityName));
  }

  /**
   * @return array<string, mixed> The non-remote params.
   */
  protected function getParams(AbstractRemoteFundingAction $action): array {
    $params = $action->getParams();
    unset($params['checkPermissions']);
    unset($params['chain']);
    unset($params['remoteContactId']);

    return $params;
  }

}
