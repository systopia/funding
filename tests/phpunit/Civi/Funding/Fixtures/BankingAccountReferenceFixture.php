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

use Civi\Api4\OptionValue;

final class BankingAccountReferenceFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @phpstan-return array<string, scalar|null>&array{id: int}
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(int $bankingAccountId, array $values = []): array {
    $result = civicrm_api3('BankingAccountReference', 'create', $values + [
      'reference' => 'DE07123412341234123412',
      'reference_type_id' => self::getReferenceTypeId('IBAN'),
      'ba_id' => $bankingAccountId,
      'sequential' => 1,
    ]);

    // @phpstan-ignore-next-line
    $values = $result['values'][0];
    $values['id'] = (int) $values['id'];

    return $values;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private static function getReferenceTypeId(string $name): ?int {
    return OptionValue::get(FALSE)
      ->addSelect('value')->addSelect('id')
      ->addWhere('option_group_id:name', '=', 'civicrm_banking.reference_types')
      ->addWhere('name', '=', $name)
      ->execute()
      ->first()['id'] ?? NULL;
  }

}
