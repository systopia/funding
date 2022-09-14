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

use Civi\Api4\FundingCaseContactRelation;

final class FundingCaseContactRelationFixture {

  /**
   * @phpstan-param array<string>|null $permissions
   *
   * @phpstan-return array<string, scalar|null>&array{id: int}
   *
   * @throws \API_Exception
   */
  public static function addContact(int $contactId, int $fundingCaseId, ?array $permissions): array {
    return FundingCaseContactRelation::create()
      ->setValues([
        'funding_case_id' => $fundingCaseId,
        'entity_table' => 'civicrm_contact',
        'entity_id' => $contactId,
        'permissions' => $permissions,
      ])->execute()->first();
  }

}
