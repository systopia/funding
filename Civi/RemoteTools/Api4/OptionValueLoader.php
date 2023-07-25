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

final class OptionValueLoader implements OptionValueLoaderInterface {

  private Api4Interface $api4;

  /**
   * @phpstan-var array<string, array<string, string|null>>
   */
  private array $values = [];

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function getOptionValue(string $groupName, string $optionName): ?string {
    if (array_key_exists($optionName, $this->values[$groupName] ?? [])) {
      return $this->values[$groupName][$optionName];
    }

    $action = $this->api4->createGetAction('OptionValue')
      ->setCheckPermissions(FALSE)
      ->addSelect('value')
      ->addWhere('option_group_id:name', '=', $groupName)
      ->addWhere('name', '=', $optionName);
    $result = $this->api4->executeAction($action);

    $this->values[$groupName] ??= [];

    return $this->values[$groupName][$optionName] ??= $result->first()['value'] ?? NULL;
  }

}
