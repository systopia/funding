<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Api4\Traits;

use Civi\Funding\Api4\RemoteApiConstants;

/**
 * Adds permissions fields in GetFieldsAction.
 */
trait PermissionsGetFieldsActionTrait {

  /**
   * @phpstan-return array<string, string>
   */
  abstract protected function getPossiblePermissions(): array;

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    return array_merge(parent::getRecords(), $this->getPermissionsFields());
  }

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  private function getPermissionsFields(): array {
    if (!str_starts_with($this->action, 'get')) {
      return [];
    }

    $possiblePermissions = $this->getPossiblePermissions();
    return array_merge([
      [
        'name' => 'permissions',
        'type' => 'Extra',
        'data_type' => 'String',
        'readonly' => TRUE,
        'serialize' => \CRM_Core_DAO::SERIALIZE_JSON,
        'options' => $possiblePermissions,
      ],
    ], $this->getFlattenedPermissionsFields($possiblePermissions));
  }

  /**
   * @phpstan-param array<string> $possiblePermissions
   *
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   *   Flattened permissions might be useful for some frontends (e.g. Drupal Views).
   */
  private function getFlattenedPermissionsFields(array $possiblePermissions): array {
    $fields = [];

    foreach ($possiblePermissions as $permission => $label) {
      $fields[] = [
        'name' => RemoteApiConstants::PERMISSION_FIELD_PREFIX . $permission,
        'description' => $label,
        'type' => 'Extra',
        'data_type' => 'Boolean',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'operators' => [],
      ];
    }

    return $fields;
  }

}
