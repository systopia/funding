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

namespace Civi\Funding\Event\FundingCase;

use Civi\Api4\FundingCase;
use Civi\Funding\Event\Permission\AbstractGetPermissionsEvent;

/**
 * Note: The result is cached. This means that permissions may not be fully
 * dynamic, and it has to be assured that the cache is cleared if necessary.
 *
 * @see \Civi\Api4\FundingCasePermissionsCache
 * @see \Civi\Funding\EventSubscriber\FundingCase\FundingCasePermissionsCacheClearSubscriber
 */
final class GetPermissionsEvent extends AbstractGetPermissionsEvent {

  public function __construct(int $entityId, int $contactId) {
    parent::__construct(FundingCase::getEntityName(), $entityId, $contactId);
  }

}
