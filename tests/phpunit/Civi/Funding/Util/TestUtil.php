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

namespace Civi\Funding\Util;

final class TestUtil {

  /**
   * Filters out extra entity fields added on create action since CiviCRM 5.53.
   *
   * @phpstan-param array<string, mixed> $values
   *
   * @phpstan-return array<string, mixed>
   */
  public static function filterCiviExtraFields(array $values): array {
    return array_filter(
      $values,
      fn (string $key) => 'custom' !== $key && 'check_permissions' !== $key,
      ARRAY_FILTER_USE_KEY
    );
  }

}
