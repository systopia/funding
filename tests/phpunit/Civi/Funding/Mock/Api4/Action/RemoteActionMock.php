<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Mock\Api4\Action;

use Civi\Funding\Api4\Action\Remote\RemoteFundingActionInterface;
use Civi\Funding\Api4\Action\Remote\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\RemoteTools\Api4\Action\Traits\EventActionTrait;

class RemoteActionMock extends StandardActionMock implements RemoteFundingActionInterface {

  use EventActionTrait;

  use RemoteFundingActionContactIdRequiredTrait;

  public function __construct(string $entityName = 'RemoteTestEntity', string $actionName = 'get') {
    parent::__construct($entityName, $actionName);
  }

  public function setContactId(int $contactId): self {
    $this->_extraParams['contactId'] = $contactId;

    return $this;
  }

}
