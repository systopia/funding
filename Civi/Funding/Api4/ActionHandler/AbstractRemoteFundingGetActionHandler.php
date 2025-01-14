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

namespace Civi\Funding\Api4\ActionHandler;

use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Remote\RemoteFundingGetAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

abstract class AbstractRemoteFundingGetActionHandler implements ActionHandlerInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(RemoteFundingGetAction $action): Result {
    $params = [
      'language' => $action->getLanguage(),
      'select' => $action->getSelect(),
      'where' => $action->getWhere(),
      'orderBy' => $action->getOrderBy(),
      'limit' => $action->getLimit(),
      'offset' => $action->getOffset(),
    ];

    $join = $this->getJoin($action);
    if ($join !== []) {
      $params['join'] = $join;
    }

    return $this->api4->execute($this->getEntityName(), 'get', $params);
  }

  /**
   * @return string The non-remote entity name.
   */
  abstract protected function getEntityName(): string;

  /**
   * @phpstan-return array<mixed>
   *   APIv4 join parameter.
   */
  protected function getJoin(RemoteFundingGetAction $action): array {
    return [];
  }

}
