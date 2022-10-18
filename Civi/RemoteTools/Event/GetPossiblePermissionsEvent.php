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

class GetPossiblePermissionsEvent extends Event {

  private string $entityName;

  /**
   * @phpstan-var array<string>
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

  public function addPermission(string $permission): self {
    $this->addPermissions([$permission]);

    return $this;
  }

  /**
   * @phpstan-param array<string> $permissions
   */
  public function addPermissions(array $permissions): self {
    $this->permissions = \array_unique(\array_merge($this->permissions, $permissions));

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
