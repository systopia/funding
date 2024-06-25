<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\JsonSchema\FinancePlanItem;

final class ArgumentAssert {

  /**
   * @throws \InvalidArgumentException
   */
  public static function assertIdentifier(string $identifier): void {
    if (1 !== preg_match('/^[a-zA-Z0-9._\-]+$/', $identifier)) {
      throw new \InvalidArgumentException(
        'identifier must not be empty and only consist of letters, numbers, ".", "_", and "-"'
      );
    }
  }

  /**
   * @param string $type
   *   May contain an optional subtype after "." or "/".
   */
  public static function assertType(string $type): void {
    if (1 !== preg_match('#^[a-zA-Z0-9_\-]+([/\.][a-zA-Z0-9_\-]+)?$#', $type)) {
      throw new \InvalidArgumentException(
        'type must not be empty and match the regular expression "^[a-zA-Z0-9_\-]+([/\.][a-zA-Z0-9_\-]+)?$"'
      );
    }
  }

}
