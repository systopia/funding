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

namespace Civi\Funding\Api4\Util;

/**
 * @codeCoverageIgnore
 */
final class SelectUtil {

  /**
   * Similar to \Civi\Api4\Utils\SelectUtil::isFieldSelected(), but also
   * returns TRUE if $select is empty and $field is not from a joined entity
   * (i.e. contains no ".").
   *
   * @phpstan-param array<string> $select
   */
  public static function isFieldSelected(string $field, array $select): bool {
    return [] === $select && !str_contains($field, '.')
      || \Civi\Api4\Utils\SelectUtil::isFieldSelected($field, $select);
  }

}
