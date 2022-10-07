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

  private const PERMISSION_ACTIONS_MAP = [
    'create_application' => ['save'],
    'modify_application' => [],
    'apply_application' => ['apply'],
    'delete_application' => [],
    'withdraw_application' => [],
  ];

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'new' => [
      'create_application' => [],
      'modify_application' => ['save'],
      'apply_application' => ['apply'],
      'delete_application' => ['delete'],
      'withdraw_application' => [],
    ],
    'applied' => [
      'create_application' => [],
      'modify_application' => ['modify'],
      'apply_application' => [],
      'delete_application' => [],
      'withdraw_application' => ['withdraw'],
    ],
    'draft' => [
      'create_application' => [],
      'modify_application' => ['save'],
      'apply_application' => ['apply'],
      'delete_application' => [],
      'withdraw_application' => ['withdraw'],
    ],
  ];

  private ApplicationProcessActionsDeterminer $actionsDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminer = new ApplicationProcessActionsDeterminer();
  }

  public function testGetActions(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      foreach ($permissionActionsMap as $permission => $actions) {
        static::assertSame($actions, $this->actionsDeterminer->getActions($status, [$permission]));
      }
    }
  }

  public function testGetActionsAll(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      $actions = array_merge(...array_values($permissionActionsMap));
      $permissions = array_keys($permissionActionsMap);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($status, $permissions));
    }
  }

  public function testGetActionsForNew(): void {
    foreach (self::PERMISSION_ACTIONS_MAP as $permission => $actions) {
      static::assertSame($actions, $this->actionsDeterminer->getActionsForNew([$permission]));
    }
  }

  public function testGetActionsForNewAllPermissions(): void {
    $actions = array_merge(...array_values(self::PERMISSION_ACTIONS_MAP));
    $permissions = array_keys(self::PERMISSION_ACTIONS_MAP);
    static::assertEquals($actions, $this->actionsDeterminer->getActionsForNew($permissions));
  }

  public function testIsActionAllowed(): void {
    static::assertTrue($this->actionsDeterminer->isActionAllowed('save', 'new', ['modify_application']));
    static::assertFalse($this->actionsDeterminer->isActionAllowed('apply', 'new', ['modify_application']));

    static::assertTrue($this->actionsDeterminer->isActionAllowed('apply', 'new', ['apply_application']));
    static::assertFalse($this->actionsDeterminer->isActionAllowed('save', 'draft', ['apply_application']));
  }

  public function testIsSaveAllowed(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      foreach ($permissionActionsMap as $permission => $actions) {
        static::assertSame(
          in_array('save', $actions, TRUE),
          $this->actionsDeterminer->isEditAllowed($status, [$permission])
        );
      }
    }
  }

}
