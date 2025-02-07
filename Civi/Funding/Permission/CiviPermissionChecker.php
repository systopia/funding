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

namespace Civi\Funding\Permission;

use Civi\RemoteTools\RequestContext\RequestContextInterface;

class CiviPermissionChecker {

  private RequestContextInterface $requestContext;

  public function __construct(RequestContextInterface $requestContext) {
    $this->requestContext = $requestContext;
  }

  /**
   * @phpstan-param string|list<string|list<string>> $permissions
   *
   * @see \CRM_Core_Permission::check()
   */
  public function checkPermission($permissions, ?int $contactId = NULL): bool {
    return \CRM_Core_Permission::check($permissions, $contactId ?? $this->requestContext->getContactId());
  }

}
