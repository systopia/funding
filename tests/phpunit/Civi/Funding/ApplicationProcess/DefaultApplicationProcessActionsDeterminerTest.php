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

use Civi\Funding\ApplicationProcess\ActionsDeterminer\DefaultApplicationProcessActionsDeterminer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminer
 * @covers \Civi\Funding\ApplicationProcess\ActionsDeterminer\DefaultApplicationProcessActionsDeterminer
 */
final class DefaultApplicationProcessActionsDeterminerTest extends TestCase {

  private const INITIAL_PERMISSION_ACTIONS_MAP = [
    'application_create' => ['save'],
    'application_modify' => [],
    'application_apply' => ['apply'],
    'application_withdraw' => [],
    'review_calculative' => [],
    'review_content' => [],
  ];

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'new' => [
      'application_create' => [],
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['delete'],
      'review_calculative' => [],
      'review_content' => [],
    ],
    'applied' => [
      'application_create' => [],
      'application_modify' => ['modify'],
      'application_apply' => [],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review'],
      'review_content' => ['review'],
    ],
    'review' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['approve-calculative', 'reject-calculative'],
      'review_content' => ['approve-content', 'reject-content'],
    ],
    'draft' => [
      'application_create' => [],
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => [],
      'review_content' => [],
    ],
    'pre-approved' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => ['approve', 'reject'],
      'application_withdraw' => [],
      'review_calculative' => [],
      'review_content' => [],
    ],
  ];

  private DefaultApplicationProcessActionsDeterminer $actionsDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminer = new DefaultApplicationProcessActionsDeterminer();
  }

  public function testGetActions(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      foreach ($permissionActionsMap as $permission => $actions) {
        static::assertSame(
          $actions,
          $this->actionsDeterminer->getActions($status, [$permission]),
          sprintf('Status: %s, permission: %s', $status, $permission)
        );
      }
    }
  }

  public function testGetActionsAll(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      $actions = array_unique(array_merge(...array_values($permissionActionsMap)));
      $permissions = array_keys($permissionActionsMap);
      static::assertEquals(
        $actions,
        $this->actionsDeterminer->getActions($status, $permissions),
        sprintf('Status: %s, permissions: %s', $status, var_export($permissions, TRUE))
      );
    }
  }

  public function testGetInitialActions(): void {
    foreach (self::INITIAL_PERMISSION_ACTIONS_MAP as $permission => $actions) {
      static::assertSame($actions, $this->actionsDeterminer->getInitialActions([$permission]));
    }
  }

  public function testGetInitialActionsAllPermissions(): void {
    $actions = array_merge(...array_values(self::INITIAL_PERMISSION_ACTIONS_MAP));
    $permissions = array_keys(self::INITIAL_PERMISSION_ACTIONS_MAP);
    static::assertEquals($actions, $this->actionsDeterminer->getInitialActions($permissions));
  }

  public function testIsActionAllowed(): void {
    static::assertTrue($this->actionsDeterminer->isActionAllowed('save', 'new', ['application_modify']));
    static::assertFalse($this->actionsDeterminer->isActionAllowed('apply', 'new', ['application_modify']));

    static::assertTrue($this->actionsDeterminer->isActionAllowed('apply', 'new', ['application_apply']));
    static::assertFalse($this->actionsDeterminer->isActionAllowed('save', 'draft', ['application_apply']));
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
