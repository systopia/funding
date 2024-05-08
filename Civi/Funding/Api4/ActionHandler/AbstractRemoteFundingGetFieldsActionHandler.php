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
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Action\AbstractRemoteGetFieldsAction;
use Civi\RemoteTools\Api4\Api4Interface;

abstract class AbstractRemoteFundingGetFieldsActionHandler implements ActionHandlerInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function getFields(AbstractRemoteGetFieldsAction $action): Result {
    return $this->api4->execute($this->getEntityName(), 'getFields', [
      'loadOptions' => $action->getLoadOptions(),
      'action' => $action->getAction(),
      'values' => $action->getValues(),
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
