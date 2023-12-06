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

namespace Civi\Funding\Api4\Query;

use Civi\Api4\Query\Api4SelectQuery;

/**
 * Can be used in the 'sql_renderer' attribute of APIv4 field spec, e.g. for
 * aliased joined fields.
 */
final class AliasSqlRenderer {

  private string $aliasedFieldName;

  public function __construct(string $aliasedFieldName) {
    $this->aliasedFieldName = $aliasedFieldName;
  }

  /**
   * @phpstan-param array<string, mixed> $field
   *
   * @throws \CRM_Core_Exception
   */
  public function __invoke(array $field, Api4SelectQuery $query): string {
    /** @phpstan-var array{sql_name: string} $aliasedField */
    $aliasedField = $query->getField($this->aliasedFieldName, TRUE);

    return $aliasedField['sql_name'];
  }

}
