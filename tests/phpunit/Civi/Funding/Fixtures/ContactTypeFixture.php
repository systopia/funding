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

use Civi\Api4\ContactType;

final class ContactTypeFixture {

  public const CONTACT_TYPE_INDIVIDUAL_ID = 1;

  public const CONTACT_TYPE_ORGANIZATION_ID = 3;

  /**
   * @param array<string, scalar> $values
   *
   * @return array
   * @phpstan-return array<string, scalar|null>&array{id: int}
   *
   * @throws \API_Exception
   */
  public static function addFixture(array $values = []): array {
    return ContactType::create(FALSE)
      ->setValues($values + [
        'name' => 'TestContactType',
        'label' => 'test contact type',
        'parent_id' => self::CONTACT_TYPE_ORGANIZATION_ID,
      ])->execute()->first();
  }

  /**
   * @return array<string, scalar|null>&array{id: int}
   *
   * @throws \API_Exception
   */
  public static function addIndividualFixture(string $name = 'TestIndividualContactType',
    string $label = 'test individual contact type'
  ): array {
    return self::addFixture(['name' => $name, 'label' => $label, 'parent_id' => self::CONTACT_TYPE_INDIVIDUAL_ID]);
  }

  /**
   * @return array<string, scalar|null>&array{id: int}
   *
   * @throws \API_Exception
   */
  public static function addOrganizationFixture(string $name = 'TestOrganizationContactType',
    string $label = 'test organization contact type'
  ): array {
    return self::addFixture(['name' => $name, 'label' => $label, 'parent_id' => self::CONTACT_TYPE_ORGANIZATION_ID]);
  }

}
