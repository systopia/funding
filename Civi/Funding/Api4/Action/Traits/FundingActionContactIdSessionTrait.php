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

namespace Civi\Funding\Api4\Action\Traits;

use Webmozart\Assert\Assert;

/**
 * Trait for actions that fetches the contact ID from the session.
 *
 * @see \Civi\Funding\Api4\Action\FundingContactIdSessionAwareInterface
 */
trait FundingActionContactIdSessionTrait {

  private ?int $contactId = NULL;

  public function getContactId(): int {
    if (NULL === $this->contactId) {
      if ($this->isRemoteApiRequest()) {
        $contactId = \CRM_Core_Session::singleton()->get('contactId', 'funding');
        Assert::integer($contactId, 'Resolved contact ID missing');
        $this->contactId = $contactId;
      }
      else {
        $this->contactId = \CRM_Core_Session::getLoggedInContactID() ?? 0;
      }
    }

    return $this->contactId;
  }

  private function isRemoteApiRequest(): bool {
    return (bool) \CRM_Core_Session::singleton()->get('isRemote', 'funding');
  }

}
