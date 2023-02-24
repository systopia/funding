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

namespace Civi\Funding\Mock\Session;

use Civi\Funding\Session\FundingSessionInterface;

final class TestFundingSession implements FundingSessionInterface {

  private int $contactId;

  private bool $remote;

  public static function newInternal(int $contactId = 1): self {
    return new static($contactId, FALSE);
  }

  public static function newRemote(int $resolvedContactId = 1): self {
    return new static($resolvedContactId, TRUE);
  }

  public function __construct(int $contactId, bool $remote) {
    $this->contactId = $contactId;
    $this->remote = $remote;
  }

  /**
   * @inheritDoc
   */
  public function getContactId(): int {
    return $this->contactId;
  }

  /**
   * @inheritDoc
   */
  public function setResolvedContactId(int $contactId): void {
    $this->contactId = $contactId;
  }

  public function isRemote(): bool {
    return $this->remote;
  }

  public function setRemote(bool $remote): void {
    $this->remote = $remote;
  }

}
