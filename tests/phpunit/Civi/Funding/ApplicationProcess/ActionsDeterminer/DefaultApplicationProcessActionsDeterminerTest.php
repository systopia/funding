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
 * @covers \Civi\Funding\ApplicationProcess\ActionsDeterminer\AbstractApplicationProcessActionsDeterminer
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

  // phpcs:disable Generic.Files.LineLength.TooLong
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
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'review' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['request-change', 'update', 'reject', 'add-comment', 'approve-calculative', 'reject-calculative'],
      'review_content' => ['request-change', 'update', 'reject', 'add-comment', 'approve-content', 'reject-content'],
    ],
    'draft' => [
      'application_create' => [],
      'application_modify' => ['save'],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'eligible' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
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
    'complete' => [
      'application_create' => [],
      'application_modify' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => [],
      'review_content' => [],
    ],
  ];
  // phpcs:enable

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
          $this->actionsDeterminer->getActions($fullStatus, [], [$permission]),
          sprintf('Status: %s, permission: %s', $status, $permission)
        );
      }
    }
  }

  public function testGetActionsAll(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      $fullStatus = new FullApplicationProcessStatus($status, NULL, NULL);
      $actions = array_values(array_unique(array_merge(...array_values($permissionActionsMap))));
      $permissions = array_keys($permissionActionsMap);
      static::assertEquals(
        $actions,
        $this->actionsDeterminer->getActions($fullStatus, [], $permissions),
        sprintf('Status: %s, permissions: %s', $status, var_export($permissions, TRUE))
      );
    }
  }

  public function testGetActionsApprove(): void {
    foreach (['review_calculative', 'review_content'] as $permission) {
      $fullStatus = new FullApplicationProcessStatus('complete', TRUE, TRUE);
      static::assertNotContains('approve', $this->actionsDeterminer->getActions($fullStatus, [], [$permission]));
      $fullStatus = new FullApplicationProcessStatus('review', TRUE, NULL);
      static::assertNotContains('approve', $this->actionsDeterminer->getActions($fullStatus, [], [$permission]));
      $fullStatus = new FullApplicationProcessStatus('review', NULL, TRUE);
      static::assertNotContains('approve', $this->actionsDeterminer->getActions($fullStatus, [], [$permission]));

      $fullStatus = new FullApplicationProcessStatus('review', TRUE, TRUE);
      static::assertContains('approve', $this->actionsDeterminer->getActions($fullStatus, [], [$permission]));
    }
  }

  public function testActionsReviewCalculative(): void {
    $permissions = ['review_calculative'];
    $fullStatus = new FullApplicationProcessStatus('review', NULL, NULL);
    static::assertContains('approve-calculative', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertContains('reject-calculative', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains('approve-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains('reject-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    $fullStatus = new FullApplicationProcessStatus('review', FALSE, NULL);
    static::assertContains('approve-calculative', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
    static::assertNotContains('approve-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains('reject-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    $fullStatus = new FullApplicationProcessStatus('review', TRUE, NULL);
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
    static::assertContains('reject-calculative', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains('approve-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains('reject-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
  }

  public function testActionsReviewContent(): void {
    $permissions = ['review_content'];
    $fullStatus = new FullApplicationProcessStatus('review', NULL, NULL);
    static::assertContains('approve-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertContains('reject-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
    static::assertNotContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
    $fullStatus = new FullApplicationProcessStatus('review', NULL, FALSE);
    static::assertContains('approve-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains(
      'reject-content',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
    static::assertNotContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
    $fullStatus = new FullApplicationProcessStatus('review', NULL, TRUE);
    static::assertNotContains('approve-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertContains('reject-content', $this->actionsDeterminer->getActions($fullStatus, [], $permissions));
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
    static::assertNotContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($fullStatus, [], $permissions)
    );
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
    static::assertTrue($this->actionsDeterminer->isActionAllowed('save', $fullStatus, [], ['application_modify']));
    static::assertFalse($this->actionsDeterminer->isActionAllowed('apply', $fullStatus, [], ['application_modify']));

    static::assertTrue($this->actionsDeterminer->isActionAllowed('apply', $fullStatus, [], ['application_apply']));
    $fullStatus = new FullApplicationProcessStatus('draft', NULL, NULL);
    static::assertFalse($this->actionsDeterminer->isActionAllowed('save', $fullStatus, [], ['application_apply']));
  }

  public function testIsAnyActionAllowed(): void {
    $fullStatus = new FullApplicationProcessStatus('new', NULL, NULL);
    static::assertTrue(
      $this->actionsDeterminer->isAnyActionAllowed(['save', 'modify'], $fullStatus, [], ['application_modify'])
    );
    static::assertFalse(
      $this->actionsDeterminer->isAnyActionAllowed(['apply', 'modify'], $fullStatus, [], ['application_modify'])
    );
  }

  public function testIsEditAllowed(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      $fullStatus = new FullApplicationProcessStatus($status, NULL, NULL);
      foreach ($permissionActionsMap as $permission => $actions) {
        static::assertSame(
          in_array('save', $actions, TRUE) || in_array('apply', $actions, TRUE) || in_array('update', $actions, TRUE),
          $this->actionsDeterminer->isEditAllowed($fullStatus, [], [$permission])
        );
      }
    }
  }

}
