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

namespace Civi\Funding\ApplicationProcess\ActionsDeterminer;

use Civi\Funding\Entity\FullApplicationProcessStatus;
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
      'review_calculative' => ['update'],
      'review_content' => ['update'],
    ],
    'applied' => [
      'application_create' => [],
      'application_modify' => ['modify'],
      'application_apply' => [],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review', 'update'],
      'review_content' => ['review', 'update'],
    ],
    'review' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['set-calculative-review-result', 'request-change', 'update'],
      'review_content' => ['set-content-review-result', 'request-change', 'update'],
    ],
    'draft' => [
      'application_create' => [],
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['update'],
      'review_content' => ['update'],
    ],
    'approved' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['update'],
      'review_content' => ['update'],
    ],
    'withdrawn' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => [],
      'review_content' => [],
    ],
    'rejected' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => [],
      'review_content' => [],
    ],
    'final' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
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
      $fullStatus = new FullApplicationProcessStatus($status, NULL, NULL);
      foreach ($permissionActionsMap as $permission => $actions) {
        static::assertSame(
          $actions,
          $this->actionsDeterminer->getActions($fullStatus, [$permission]),
          sprintf('Status: %s, permission: %s', $status, $permission)
        );
      }
    }
  }

  public function testGetActionsAll(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      $fullStatus = new FullApplicationProcessStatus($status, NULL, NULL);
      $actions = array_unique(array_merge(...array_values($permissionActionsMap)));
      $permissions = array_keys($permissionActionsMap);
      static::assertEquals(
        $actions,
        $this->actionsDeterminer->getActions($fullStatus, $permissions),
        sprintf('Status: %s, permissions: %s', $status, var_export($permissions, TRUE))
      );
    }
  }

  public function testGetActionsApprove(): void {
    foreach (['review_calculative', 'review_content'] as $permission) {
      $fullStatus = new FullApplicationProcessStatus('final', TRUE, TRUE);
      static::assertSame([], $this->actionsDeterminer->getActions($fullStatus, [$permission]));

      $actions = self::STATUS_PERMISSION_ACTIONS_MAP['review'][$permission];
      $fullStatus = new FullApplicationProcessStatus('review', TRUE, NULL);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('review', NULL, TRUE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));

      $actions[] = 'approve';
      $fullStatus = new FullApplicationProcessStatus('review', TRUE, TRUE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
    }
  }

  public function testGetActionsReject(): void {
    foreach (['review_calculative', 'review_content'] as $permission) {
      $fullStatus = new FullApplicationProcessStatus('final', FALSE, FALSE);
      static::assertSame([], $this->actionsDeterminer->getActions($fullStatus, [$permission]));

      $actions = self::STATUS_PERMISSION_ACTIONS_MAP['review'][$permission];
      $fullStatus = new FullApplicationProcessStatus('review', NULL, NULL);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));

      $actions[] = 'reject';
      $fullStatus = new FullApplicationProcessStatus('review', FALSE, FALSE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('review', NULL, FALSE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('review', TRUE, FALSE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('review', FALSE, TRUE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('review', FALSE, NULL);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
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
    $fullStatus = new FullApplicationProcessStatus('new', NULL, NULL);
    static::assertTrue($this->actionsDeterminer->isActionAllowed('save', $fullStatus, ['application_modify']));
    static::assertFalse($this->actionsDeterminer->isActionAllowed('apply', $fullStatus, ['application_modify']));

    static::assertTrue($this->actionsDeterminer->isActionAllowed('apply', $fullStatus, ['application_apply']));
    $fullStatus = new FullApplicationProcessStatus('draft', NULL, NULL);
    static::assertFalse($this->actionsDeterminer->isActionAllowed('save', $fullStatus, ['application_apply']));
  }

  public function testIsAnyActionAllowed(): void {
    $fullStatus = new FullApplicationProcessStatus('new', NULL, NULL);
    static::assertTrue(
      $this->actionsDeterminer->isAnyActionAllowed(['save', 'modify'], $fullStatus, ['application_modify'])
    );
    static::assertFalse(
      $this->actionsDeterminer->isAnyActionAllowed(['apply', 'modify'], $fullStatus, ['application_modify'])
    );
  }

  public function testIsEditAllowed(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      $fullStatus = new FullApplicationProcessStatus($status, NULL, NULL);
      foreach ($permissionActionsMap as $permission => $actions) {
        static::assertSame(
          in_array('save', $actions, TRUE) || in_array('apply', $actions, TRUE) || in_array('update', $actions, TRUE),
          $this->actionsDeterminer->isEditAllowed($fullStatus, [$permission])
        );
      }
    }
  }

}
