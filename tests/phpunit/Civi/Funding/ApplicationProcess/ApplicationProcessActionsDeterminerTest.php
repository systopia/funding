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

namespace Civi\Funding\ApplicationProcess;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationProcessActionsDeterminer
 */
final class ApplicationProcessActionsDeterminerTest extends TestCase {

  private const PERMISSIONS_ACTIONS_NEW_MAP = [
    'create_application' => ['save' => 'Save'],
    'apply_application' => ['apply' => 'Apply'],
    'modify_application' => [],
  ];

  private ApplicationProcessActionsDeterminer $actionsDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminer = new ApplicationProcessActionsDeterminer();
  }

  public function testGetActions(): void {
    static::markTestIncomplete();
  }

  public function testGetActionsForNew(): void {
    foreach (self::PERMISSIONS_ACTIONS_NEW_MAP as $permission => $actions) {
      static::assertSame($actions, $this->actionsDeterminer->getActionsForNew([$permission]));
    }
  }

  public function testGetActionsForNewAllPermissions(): void {
    $actions = array_merge(...array_values(self::PERMISSIONS_ACTIONS_NEW_MAP));
    $permissions = array_keys(self::PERMISSIONS_ACTIONS_NEW_MAP);
    static::assertEquals($actions, $this->actionsDeterminer->getActionsForNew($permissions));
  }

  public function testIsModifyAllowed(): void {
    static::assertTrue($this->actionsDeterminer->isModifyAllowed('new', ['modify_application']));
    static::assertFalse($this->actionsDeterminer->isModifyAllowed('new', ['create_application']));
  }

}
