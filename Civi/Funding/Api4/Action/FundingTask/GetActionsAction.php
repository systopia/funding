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

namespace Civi\Funding\Api4\Action\FundingTask;

use Civi\Api4\Action\GetActions;
use Civi\Api4\FundingTask;

final class GetActionsAction extends GetActions {

  public function __construct() {
    parent::__construct(FundingTask::getEntityName(), 'getActions');
  }

  /**
   * @return list<mixed>|null
   */
  protected function _itemsToGet($field): ?array {
    $itemsToGet = parent::_itemsToGet($field);
    if ('name' === $field && NULL === $itemsToGet) {
      // Prevent BadMethodCallException that would be thrown when the action
      // methods that throw this exception would be called.
      $itemsToGet = ['create', 'delete', 'get', 'getActions', 'getFields', 'update'];
    }

    // @phpstan-ignore return.type
    return $itemsToGet;
  }

}
