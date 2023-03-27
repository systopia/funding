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

namespace Civi\RemoteTools\Api4;

use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class OptionValueLoader implements OptionValueLoaderInterface {

  private Api4Interface $api4;

  /**
   * @phpstan-var array<array<int>>
   */
  private array $ids = [];

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function getId(string $groupName, string $valueName): ?int {
    return $this->ids[$groupName][$valueName] ??= $this->api4->execute(
      'OptionValue',
      'get',
      [
        'select' => ['id'],
        'where' => [
          CompositeCondition::fromFieldValuePairs([
            'option_group_id.name' => $groupName,
            'name' => $valueName,
          ])->toArray(),
        ],
        'checkPermissions' => FALSE,
      ],
    )->first()['id'] ?? NULL;
  }

}
