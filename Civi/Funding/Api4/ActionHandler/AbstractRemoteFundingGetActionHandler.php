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
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingGetAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

abstract class AbstractRemoteFundingGetActionHandler implements ActionHandlerInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function get(AbstractRemoteFundingGetAction $action): Result {
    return $this->api4->execute($this->getEntityName(), 'get', [
      'language' => $action->getLanguage(),
      'select' => $action->getSelect(),
      'where' => $action->getWhere(),
      'orderBy' => $action->getOrderBy(),
      'limit' => $action->getLimit(),
      'offset' => $action->getOffset(),
    ]);
  }

  abstract protected function getEntityName(): string;

}
