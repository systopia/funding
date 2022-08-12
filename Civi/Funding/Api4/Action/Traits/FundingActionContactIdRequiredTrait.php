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

trait FundingActionContactIdRequiredTrait {

  /**
   * Must be initialized because it is directly accessed in AbstractAction.
   *
   * @var int|null
   * @required
   */
  protected ?int $contactId = NULL;

  public function setContactId(int $contactId): self {
    $this->contactId = $contactId;

    return $this;
  }

  public function getContactId(): int {
    if (NULL === $this->contactId) {
      /*
       * CiviCRM executes internal get actions via AbstractBatchAction (used for
       * example in DAODeleteAction). In this case additional parameters are not
       * set. So if this action is not the result of some remote API request we
       * use the contact ID of the logged-in user or 0 if there's no logged-in
       * user (e.g. on CLI).
       */
      if (!$this->isRemoteApiRequest()) {
        $this->contactId = \CRM_Core_Session::getLoggedInContactID() ?? 0;
      }
      Assert::notNull($this->contactId);
    }

    return $this->contactId;
  }

  private function isRemoteApiRequest(): bool {
    if ('cli' === PHP_SAPI && !isset($_SERVER['REQUEST_URI'])) {
      // No HTTP request
      return FALSE;
    }

    if (str_contains($_SERVER['REQUEST_URI'] ?? '', '/Remote')) {
      // APIv4
      return TRUE;
    }

    if (str_starts_with($_REQUEST['entity'] ?? '', 'Remote')) {
      // APIv3
      return TRUE;
    }

    return FALSE;
  }

}
