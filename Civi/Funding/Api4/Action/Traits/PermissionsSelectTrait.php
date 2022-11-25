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

trait PermissionsSelectTrait {

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    $fields = parent::getRecords();
    $permissionsFieldName = $this->getPermissionsFieldName();
    foreach ($fields as &$field) {
      if ($permissionsFieldName === $field['name']) {
        $field['input_type'] = 'Select';
        /** @phpstan-ignore-next-line */
        $field['input_attrs']['multiple'] = TRUE;
        $field['options'] = $this->getPossiblePermissions();
      }
    }

    return $fields;
  }

  protected function getPermissionsFieldName(): string {
    return 'permissions';
  }

  /**
   * @phpstan-return array<string>
   */
  abstract protected function getPossiblePermissions(): array;

}
