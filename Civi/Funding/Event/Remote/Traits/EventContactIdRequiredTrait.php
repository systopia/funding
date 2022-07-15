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

namespace Civi\Funding\Event\Remote\Traits;

trait EventContactIdRequiredTrait {

  protected int $contactId;

  protected string $remoteContactId;

  public function getContactId(): int {
    return $this->contactId;
  }

  public function getRemoteContactId(): string {
    return $this->remoteContactId;
  }

  /**
   * @return string[]
   */
  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), ['contactId', 'remoteContactId']);
  }

}
