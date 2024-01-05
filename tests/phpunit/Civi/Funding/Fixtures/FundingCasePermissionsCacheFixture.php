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

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingCasePermissionsCache;

final class FundingCasePermissionsCacheFixture {

  /**
   * @phpstan-param list<string> $permissions
   *
   * @phpstan-return array<string, scalar|null>&array{id: int}
   *
   * @throws \CRM_Core_Exception
   */
  public static function add(
    int $fundingCaseId,
    int $contactId,
    bool $remote = FALSE,
    array $permissions = ['test']
  ): array {
    return FundingCasePermissionsCache::create(FALSE)
      ->setValues([
        'funding_case_id' => $fundingCaseId,
        'contact_id' => $contactId,
        'is_remote' => $remote,
        'permissions' => $permissions,
      ])->execute()->first();
  }

}
