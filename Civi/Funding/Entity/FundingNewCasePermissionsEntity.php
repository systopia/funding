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

namespace Civi\Funding\Entity;

/**
 * @phpstan-type newCasePermissionsT array{
 *   id?: int,
 *   type: string,
 *   properties: array<string, mixed>,
 *   permissions: list<string>,
 * }
 *
 * @phpstan-extends AbstractEntity<newCasePermissionsT>
 */
final class FundingNewCasePermissionsEntity extends AbstractEntity {

  public function getType(): string {
    return $this->values['type'];
  }

  public function setType(string $type): self {
    $this->values['type'] = $type;

    return $this;
  }

  /**
   * @phpstan-return array<string, mixed> JSON serializable.
   */
  public function getProperties(): array {
    return $this->values['properties'];
  }

  /**
   * @phpstan-param array<string, mixed> $properties JSON serializable.
   */
  public function setProperties(array $properties): self {
    $this->values['properties'] = $properties;

    return $this;
  }

  /**
   * @phpstan-return list<string>
   */
  public function getPermissions(): array {
    return $this->values['permissions'];
  }

  /**
   * @phpstan-param list<string> $permissions
   */
  public function setPermissions(array $permissions): self {
    $this->values['permissions'] = $permissions;

    return $this;
  }

}
