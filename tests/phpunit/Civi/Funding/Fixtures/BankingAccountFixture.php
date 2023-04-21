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

namespace Civi\Funding\Fixtures;

final class BankingAccountFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @phpstan-return array<string, scalar|null>&array{id: int}
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(array $values = []): array {
    $result = civicrm_api3('BankingAccount', 'create', $values + [
      'description' => 'Test',
      'created_date' => date('2023-04-05 06:07:08'),
      'modified_date' => date('2023-04-05 06:07:08'),
      'data_raw' => '{}',
      'data_parsed' => '{}',
      'contact_id' => NULL,
      'sequential' => 1,
    ]);

    // @phpstan-ignore-next-line
    $values = $result['values'][0];
    $values['id'] = (int) $values['id'];

    return $values;
  }

}
