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

use Civi\Api4\FundingCasePermissionsCache;
use Civi\Api4\Generic\DAOGetAction;

/**
 * @codeCoverageIgnore
 */
final class FundingCasePermissionsUtil {

  /**
   * Adds a join to the funding case permissions cache.
   *
   * @param string $fundingCaseIdFieldName
   *   Name of the field containing the funding case ID to join with.
   *
   * @see addPermissionsRestriction()
   */
  public static function addPermissionsCacheJoin(
    DAOGetAction $action,
    string $fundingCaseIdFieldName,
    int $contactId,
    bool $remote,
    string $tableAlias = '_pc'
  ): void {
    $action->addJoin(FundingCasePermissionsCache::getEntityName() . ' AS ' . $tableAlias, 'LEFT', NULL,
      ['_pc.funding_case_id', '=', $fundingCaseIdFieldName],
      ['_pc.contact_id', '=', $contactId],
      ['_pc.is_remote', '=', $remote],
    );
  }

  /**
   * Filters out all funding cases without permission. Funding cases for which
   * no permissions are determined, yet, are included.
   *
   * @param string $tableAlias
   *   Must be the same as used in the call of addPermissionsCacheJoin().
   *
   * @see addPermissionsCacheJoin()
   */
  public static function addPermissionsRestriction(
    DAOGetAction $action,
    string $tableAlias = '_pc'
  ): void {
    $action->addClause(
      'OR',
      [$tableAlias . '.permissions', '!=', '[]'],
      [$tableAlias . '.permissions', 'IS NULL'],
    );
  }

}
