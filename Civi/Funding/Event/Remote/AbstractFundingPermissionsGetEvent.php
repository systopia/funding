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

namespace Civi\Funding\Event\Remote;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractFundingPermissionsGetEvent extends Event {

  private int $contactId;

  private string $entityName;

  private int $entityId;

  /**
   * @var array<int, string>|null
   */
  private ?array $permissions = NULL;

  public function __construct(string $entityName, int $entityId, int $contactId) {
    $this->entityName = $entityName;
    $this->entityId = $entityId;
    $this->contactId = $contactId;
  }

  public function getContactId(): int {
    return $this->contactId;
  }

  public function getEntityName(): string {
    return $this->entityName;
  }

  public function getEntityId(): int {
    return $this->entityId;
  }

  /**
   * @param array<string> $permissions
   *
   * @return $this
   */
  public function addPermissions(array $permissions): self {
    $this->permissions = array_values(array_unique(array_merge($this->permissions ?? [], $permissions)));

    return $this;
  }

  /**
   * @return array<int, string>|null
   */
  public function getPermissions(): ?array {
    return $this->permissions;
  }

  /**
   * @param array<string>|null $permissions
   *
   * @return $this
   */
  public function setPermissions(?array $permissions): self {
    $this->permissions = NULL === $permissions ? NULL : array_values($permissions);

    return $this;
  }

}
