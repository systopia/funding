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

use Symfony\Contracts\EventDispatcher\Event;

class GetPossiblePermissionsEvent extends Event {

  private string $entityName;

  /**
   * @phpstan-var array<string, string>
   */
  private array $permissions = [];

  public static function getName(string $entityName): string {
    return static::class . '@' . $entityName;
  }

  public function __construct(string $entityName) {
    $this->entityName = $entityName;
  }

  public function getEntityName(): string {
    return $this->entityName;
  }

  public function addPermission(string $permission, string $label): self {
    $this->permissions[$permission] = $label;

    return $this;
  }

  /**
   * @phpstan-param array<string, string> $permissions
   *   Permissions mapped to labels.
   */
  public function addPermissions(array $permissions): self {
    $this->permissions = \array_merge($this->permissions, $permissions);

    return $this;
  }

  /**
   * @phpstan-return array<string, string>
   *   Permissions mapped to labels.
   */
  public function getPermissions(): array {
    return $this->permissions;
  }

  public function removePermission(string $permission): self {
    unset($this->permissions[$permission]);

    return $this;
  }

  /**
   * @phpstan-param array<string, string> $permissions
   *   Permissions mapped to labels.
   */
  public function setPermissions(array $permissions): self {
    $this->permissions = $permissions;

    return $this;
  }

}
