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

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
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
      'application_withdraw' => ['withdraw'],
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
      'application_withdraw' => ['withdraw'],
      'review_calculative' => ['update', 'add-comment'],
      'review_content' => ['update', 'add-comment'],
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
      foreach ($permissionActionsMap as $permission => $actions) {
        $applicationProcessBundle = $this->createApplicationProcessBundle($status, NULL, NULL, [$permission]);
        static::assertSame(
          $actions,
          $this->actionsDeterminer->getActions($applicationProcessBundle, []),
          sprintf('Status: %s, permission: %s', $status, $permission)
        );
      }
    }
  }

  public function testGetActionsAll(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      $actions = array_values(array_unique(array_merge(...array_values($permissionActionsMap))));
      $permissions = array_keys($permissionActionsMap);
      $applicationProcessBundle = $this->createApplicationProcessBundle($status, NULL, NULL, $permissions);
      static::assertEquals(
        $actions,
        $this->actionsDeterminer->getActions($applicationProcessBundle, []),
        sprintf('Status: %s, permissions: %s', $status, var_export($permissions, TRUE))
      );
    }
  }

  public function testGetActionsApprove(): void {
    foreach (['review_calculative', 'review_content'] as $permission) {
      $applicationProcessBundle = $this->createApplicationProcessBundle('complete', TRUE, TRUE, [$permission]);
      static::assertNotContains(
        'approve',
        $this->actionsDeterminer->getActions($applicationProcessBundle, [])
      );
      $applicationProcessBundle = $this->createApplicationProcessBundle('review', TRUE, NULL, [$permission]);
      static::assertNotContains(
        'approve',
        $this->actionsDeterminer->getActions($applicationProcessBundle, [])
      );
      $applicationProcessBundle = $this->createApplicationProcessBundle('review', NULL, TRUE, [$permission]);
      static::assertNotContains(
        'approve',
        $this->actionsDeterminer->getActions($applicationProcessBundle, [])
      );

      $applicationProcessBundle = $this->createApplicationProcessBundle('review', TRUE, TRUE, [$permission]);
      static::assertContains(
        'approve',
        $this->actionsDeterminer->getActions($applicationProcessBundle, [])
      );
    }
  }

  public function testActionsReviewCalculative(): void {
    $permissions = ['review_calculative'];
    $applicationProcessBundle = $this->createApplicationProcessBundle('review', NULL, NULL, $permissions);
    static::assertContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'approve-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'reject-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    $applicationProcessBundle = $this->createApplicationProcessBundle('review', FALSE, NULL, $permissions);
    static::assertContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'approve-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'reject-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    $applicationProcessBundle = $this->createApplicationProcessBundle('review', TRUE, NULL, $permissions);
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'approve-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'reject-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
  }

  public function testActionsReviewContent(): void {
    $permissions = ['review_content'];
    $applicationProcessBundle = $this->createApplicationProcessBundle('review', NULL, NULL, $permissions);
    static::assertContains(
      'approve-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertContains(
      'reject-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    $applicationProcessBundle = $this->createApplicationProcessBundle('review', NULL, FALSE, $permissions);
    static::assertContains(
      'approve-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'reject-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    $applicationProcessBundle = $this->createApplicationProcessBundle('review', NULL, TRUE, $permissions);
    static::assertNotContains(
      'approve-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertContains(
      'reject-content',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
    );
    static::assertNotContains(
      'reject-calculative',
      $this->actionsDeterminer->getActions($applicationProcessBundle, [])
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
    $applicationProcessBundle = $this->createApplicationProcessBundle('new', NULL, NULL, ['application_modify']);
    static::assertTrue(
      $this->actionsDeterminer->isActionAllowed('save', $applicationProcessBundle, [])
    );
    static::assertFalse(
      $this->actionsDeterminer->isActionAllowed('apply', $applicationProcessBundle, [])
    );

    $applicationProcessBundle = $this->createApplicationProcessBundle('new', NULL, NULL, ['application_apply']);
    static::assertTrue(
      $this->actionsDeterminer->isActionAllowed('apply', $applicationProcessBundle, [])
    );
    $applicationProcessBundle = $this->createApplicationProcessBundle('draft', NULL, NULL, ['application_apply']);
    static::assertFalse(
      $this->actionsDeterminer->isActionAllowed('save', $applicationProcessBundle, [])
    );
  }

  public function testIsAnyActionAllowed(): void {
    $applicationProcessBundle = $this->createApplicationProcessBundle('new', NULL, NULL, ['application_modify']);
    static::assertTrue(
      $this->actionsDeterminer->isAnyActionAllowed(
        ['save', 'modify'],
        $applicationProcessBundle,
        []
      )
    );
    static::assertFalse(
      $this->actionsDeterminer->isAnyActionAllowed(
        ['apply', 'modify'],
        $applicationProcessBundle,
        []
      )
    );
  }

  public function testIsEditAllowed(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      foreach ($permissionActionsMap as $permission => $actions) {
        $applicationProcessBundle = $this->createApplicationProcessBundle($status, NULL, NULL, [$permission]);
        static::assertSame(
          in_array('save', $actions, TRUE) || in_array('apply', $actions, TRUE) || in_array('update', $actions, TRUE),
          $this->actionsDeterminer->isEditAllowed($applicationProcessBundle, [])
        );
      }
    }
  }

  /**
   * @param string $status
   * @param bool|null $isReviewCalculative
   * @param bool|null $isReviewContent
   * @param list<string> $permissions
   */
  private function createApplicationProcessBundle(
    string $status,
    ?bool $isReviewCalculative,
    ?bool $isReviewContent,
    array $permissions
  ): ApplicationProcessEntityBundle {
    return ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => $status,
      'is_review_calculative' => $isReviewCalculative,
      'is_review_content' => $isReviewContent,
    ], ['permissions' => $permissions]);
  }

}
