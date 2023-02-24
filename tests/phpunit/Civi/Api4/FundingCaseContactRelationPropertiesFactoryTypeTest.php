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

namespace Civi\Api4;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryLocator;
use Civi\PHPUnit\Traits\ArrayAssertTrait;

/**
 * @covers \Civi\Api4\FundingCaseContactRelationPropertiesFactoryType
 * @covers \Civi\Funding\Api4\Action\FundingCaseContactRelationPropertiesFactoryType\GetAction
 * @covers \Civi\Funding\Api4\Action\FundingCaseContactRelationPropertiesFactoryType\GetFieldsAction
 *
 * @group headless
 */
final class FundingCaseContactRelationPropertiesFactoryTypeTest extends AbstractFundingHeadlessTestCase {

  use ArrayAssertTrait;

  private const FIELD_NAMES = ['name', 'label', 'template', 'help', 'extra'];

  public function testGet(): void {
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);

    /** @var \Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryLocator $factoryLocator */
    $factoryLocator = \Civi::service(RelationPropertiesFactoryLocator::class);
    /** @phpstan-var array<array<string, mixed>> $factoryTypes */
    $factoryTypes = FundingCaseContactRelationPropertiesFactoryType::get()->execute()->getArrayCopy();
    static::assertNotEmpty($factoryTypes);
    foreach ($factoryTypes as $factoryType) {
      static::assertArrayHasSameKeys(self::FIELD_NAMES, $factoryType);
      // @phpstan-ignore-next-line
      static::assertTrue($factoryLocator->has($factoryType['name']));
    }
  }

  public function testGetFields(): void {
    /** @phpstan-var array<array<string, mixed>> $fields */
    $fields = FundingCaseContactRelationPropertiesFactoryType::getFields()->execute()->getArrayCopy();
    $fieldNames = array_map(fn ($field) => $field['name'], $fields);
    static::assertArrayHasSameValues(self::FIELD_NAMES, $fieldNames);
  }

}
