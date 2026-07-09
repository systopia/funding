<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Activity\Traits;

use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Core_BAO_Domain;

trait SourceContactTrait {

  /**
   * @return int
   * @throws \CRM_Core_Exception
   * @throws \Exception
   */
  protected function getResolvedSourceContactId(): int {
    return 0 === $this->getRequestContext()->getContactId()
      ? (int) CRM_Core_BAO_Domain::getDomain()->contact_id
      : $this->getRequestContext()->getContactId();
  }

  protected function getRequestContext(): RequestContextInterface {
    return $this->requestContext;
  }

}
