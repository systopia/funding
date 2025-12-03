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

namespace Civi\Funding\Api4;

use Civi\RemoteTools\Api4\Api4Interface;

final class OptionsLoader implements OptionsLoaderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function getOptions(string $entityName, string $field): array {
    $result = $this->api4->execute($entityName, 'getFields', [
      'checkPermissions' => FALSE,
      'loadOptions' => TRUE,
      'where' => [
        ['name', '=', $field],
      ],
      'select' => [
        'options',
      ],
    ]);

    /** @var array<scalar|null, string>|bool $options */
    $options = $result->first()['options'] ?? [];

    return is_bool($options) ? [] : $options;
  }

  /**
   * @inheritDoc
   */
  public function getOptionLabel(string $entityName, string $field, string $value): ?string {
    return $this->getOptions($entityName, $field)[$value] ?? NULL;
  }

}
