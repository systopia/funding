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

namespace Civi\Funding\DocumentRender\CiviOffice;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder
 */
final class CiviOfficeContextDataHolderTest extends TestCase {

  public function test(): void {
    $contextDataHolder = new CiviOfficeContextDataHolder();
    static::assertSame([], $contextDataHolder->getEntityData('Entity', 1));
    static::assertSame('default', $contextDataHolder->getEntityDataValue('Entity', 1, 'key', 'default'));

    $contextDataHolder->addEntityData('Entity', 1, ['key' => 'value']);
    static::assertSame(['key' => 'value'], $contextDataHolder->getEntityData('Entity', 1));
    static::assertSame('value', $contextDataHolder->getEntityDataValue('Entity', 1, 'key'));
    static::assertNull($contextDataHolder->getEntityDataValue('Entity', 1, 'key2'));

    $contextDataHolder->removeEntityData('Entity', 1);
    static::assertSame([], $contextDataHolder->getEntityData('Entity', 1));
  }

}
