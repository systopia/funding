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

namespace Civi\Funding\Api4\Action\Remote;

use Civi\Api4\Action\GetActions;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Action\RemoteActionInterface;
use Civi\RemoteTools\Api4\Action\Traits\RemoteContactIdParameterOptionalTrait;
use Civi\RemoteTools\Api4\Action\Traits\ResolvedContactIdOptionalTrait;

class RemoteFundingGetActions extends GetActions implements RemoteActionInterface {

  use RemoteContactIdParameterOptionalTrait;

  use ResolvedContactIdOptionalTrait;

  public function _run(Result $result): void {
    if (NULL === $this->getRemoteContactId()) {
      // Might fail otherwise in \Civi\RemoteTools\EventSubscriber\ApiAuthorizeInitRequestSubscriber
      // because of missing extra params.
      $this->setCheckPermissions(FALSE);
    }

    parent::_run($result);
  }

}
