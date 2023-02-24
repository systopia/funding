<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Session;

use Webmozart\Assert\Assert;

final class FundingSession implements FundingSessionInterface {

  private \CRM_Core_Session $session;

  public function __construct(\CRM_Core_Session $session) {
    $this->session = $session;
  }

  public function getContactId(): int {
    if ($this->isRemote()) {
      $contactId = $this->session->get('contactId', 'funding');
      Assert::integer($contactId, 'Resolved contact ID missing');

      return $contactId;
    }

    return $this->session::getLoggedInContactID() ?? 0;
  }

  public function setResolvedContactId(int $contactId): void {
    $this->session->set('contactId', $contactId, 'funding');
  }

  public function isRemote(): bool {
    return (bool) $this->session->get('isRemote', 'funding');
  }

  public function setRemote(bool $remote): void {
    $this->session->set('isRemote', $remote, 'funding');
  }

}
