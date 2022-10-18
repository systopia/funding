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

namespace Civi\RemoteTools\Event;

use Symfony\Component\EventDispatcher\Event;

abstract class AbstractGetPermissionsEvent extends Event {

  private int $contactId;

  private string $entityName;

  private int $entityId;

  /**
   * @var array<string>
   */
  private array $permissions = [];

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
   */
  public function addPermissions(array $permissions): self {
    $this->permissions = \array_values(array_unique(array_merge($this->permissions, $permissions)));

    return $this;
  }

  /**
   * @phpstan-return array<string>
   */
  public function getPermissions(): array {
    return $this->permissions;
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  public function setPermissions(array $permissions): self {
    $this->permissions = \array_values($permissions);

    return $this;
  }

}
