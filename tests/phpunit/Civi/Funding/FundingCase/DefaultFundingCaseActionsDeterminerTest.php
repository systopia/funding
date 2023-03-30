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

namespace Civi\Funding\FundingCase;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\DefaultFundingCaseActionsDeterminer
 * @covers \Civi\Funding\FundingCase\FundingCaseActionsDeterminer
 */
final class DefaultFundingCaseActionsDeterminerTest extends TestCase {

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'open' => [
      'review_calculative' => ['approve'],
      'review_content' => ['approve'],
    ],
    'ongoing' => [
      'review_calculative' => ['recreate-transfer-contract'],
      'review_content' => ['recreate-transfer-contract'],
    ],
    'closed' => [
      'review_calculative' => [],
      'review_content' => [],
    ],
  ];

  private DefaultFundingCaseActionsDeterminer $actionsDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminer = new DefaultFundingCaseActionsDeterminer();
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
      $actions = array_values(array_unique(array_merge(...array_values($permissionActionsMap))));
      $permissions = array_keys($permissionActionsMap);
      static::assertEquals(
        $actions,
        $this->actionsDeterminer->getActions($status, $permissions),
        sprintf('Status: %s, permissions: %s', $status, var_export($permissions, TRUE))
      );
    }
  }

  public function testIsActionAllowed(): void {
    static::assertTrue($this->actionsDeterminer->isActionAllowed('approve', 'open', ['review_calculative']));
    static::assertFalse($this->actionsDeterminer->isActionAllowed('some_action', 'open', ['review_calculative']));
    static::assertFalse($this->actionsDeterminer->isActionAllowed('approve', 'ongoing', ['review_calculative']));
  }

  public function testIsAnyActionAllowed(): void {
    static::assertTrue(
      $this->actionsDeterminer->isAnyActionAllowed(['some_action', 'approve'], 'open', ['review_calculative'])
    );
    static::assertFalse(
      $this->actionsDeterminer->isAnyActionAllowed(['some_action', 'another_action'], 'open', ['review_calculative'])
    );
    static::assertFalse(
      $this->actionsDeterminer->isAnyActionAllowed(['some_action', 'approve'], 'ongoing', ['review_calculative'])
    );
  }

}
