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

namespace Civi\Api4\Query;

use CRM_Funding_ExtensionUtil as E;

/**
 * @todo Once the minimum MariaDB version is 10.9 'JSON_OVERLAPS' should be returned in getName().
 */
final class SqlFunctionFUNDING_JSON_OVERLAPS extends SqlFunction {

  protected static $category = self::CATEGORY_COMPARISON;

  protected static $dataType = 'Array';

  /**
   * @phpstan-return array<array<string, mixed>>
   */
  protected static function params(): array {
    return [
      [
        'must_be' => ['SqlField', 'SqlString', 'SqlFunction'],
        'label' => ts('json_document1'),
      ],
      [
        'must_be' => ['SqlField', 'SqlString', 'SqlFunction'],
        'label' => ts('json_document2'),
      ],
    ];
  }

  /**
   * @return string
   *
   * @codeCoverageIgnore
   */
  public static function getTitle(): string {
    return E::ts('JSON Overlaps');
  }

  /**
   * @return string
   *
   * @codeCoverageIgnore
   */
  public static function getDescription(): string {
    return E::ts(
      'Compares two JSON documents and returns true if they overlap. See https://mariadb.com/kb/en/json_overlaps/'
    );
  }

}
