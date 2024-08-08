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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ActionsDeterminer\ReworkPossibleApplicationProcessActionsDeterminer
 */
final class ReworkPossibleApplicationProcessActionsDeterminerTest extends TestCase {

  // phpcs:disable Generic.Files.LineLength.TooLong
  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'eligible' => [
      'application_modify' => [],
      'application_request_rework' => ['request-rework'],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => [],
      'review_content' => [],
    ],
    'rework-requested' => [
      'application_modify' => [],
      'application_request_rework' => ['withdraw-rework-request'],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['approve-rework-request', 'reject-rework-request', 'add-comment'],
      'review_content' => ['approve-rework-request', 'reject-rework-request', 'add-comment'],
    ],
    'rework' => [
      'application_modify' => ['save'],
      'application_request_rework' => [],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw-change'],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'rework-review-requested' => [
      'application_modify' => ['request-rework'],
      'application_request_rework' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['review', 'add-comment'],
      'review_content' => ['review', 'add-comment'],
    ],
    'rework-review' => [
      'application_modify' => [],
      'application_request_rework' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['request-change', 'update', 'reject-change', 'add-comment', 'approve-calculative', 'reject-calculative'],
      'review_content' => ['request-change', 'update', 'reject-change', 'add-comment', 'approve-content', 'reject-content'],
    ],
    'complete' => [
      'application_modify' => [],
      'application_request_rework' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => [],
      'review_content' => [],
    ],
  ];
  // phpcs:enable

  /**
   * @var \Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $decoratedActionsDeterminerMock;

  private ReworkPossibleApplicationProcessActionsDeterminer $actionsDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->decoratedActionsDeterminerMock = $this->createMock(ApplicationProcessActionsDeterminerInterface::class);
    $this->actionsDeterminer = new ReworkPossibleApplicationProcessActionsDeterminer(
      $this->decoratedActionsDeterminerMock
    );
  }

  public function testGetActions(): void {
    $this->decoratedActionsDeterminerMock->expects(static::atLeastOnce())->method('getActions')->willReturn([]);
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
    $this->decoratedActionsDeterminerMock->expects(static::atLeastOnce())->method('getActions')->willReturn([]);
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

  public function testGetActionsApproveChange(): void {
    foreach (['review_calculative', 'review_content'] as $permission) {
      $applicationProcessBundle = $this->createApplicationProcessBundle('rework', TRUE, TRUE, [$permission]);
      static::assertNotContains(
        'approve-change',
        $this->actionsDeterminer->getActions($applicationProcessBundle, [])
      );
      $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', TRUE, NULL, [$permission]);
      static::assertNotContains(
        'approve-change',
        $this->actionsDeterminer->getActions($applicationProcessBundle, [])
      );
      $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', NULL, TRUE, [$permission]);
      static::assertNotContains(
        'approve-change',
        $this->actionsDeterminer->getActions($applicationProcessBundle, [])
      );

      $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', TRUE, TRUE, [$permission]);
      static::assertContains(
        'approve-change',
        $this->actionsDeterminer->getActions($applicationProcessBundle, [])
      );
    }
  }

  public function testActionsReviewCalculative(): void {
    $permissions = ['review_calculative'];
    $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', NULL, NULL, $permissions);
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
    $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', FALSE, NULL, $permissions);
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
    $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', TRUE, NULL, $permissions);
    static::assertNotContains(
      'approve-calculative',
      $this->actionsDeterminer->getActions(
        $applicationProcessBundle,
        []
      )
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
    $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', NULL, NULL, $permissions);
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
    $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', NULL, FALSE, $permissions);
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
    $applicationProcessBundle = $this->createApplicationProcessBundle('rework-review', NULL, TRUE, $permissions);
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

  public function testGetActionsDecorated(): void {
    $applicationProcessBundle = $this->createApplicationProcessBundle('foo', NULL, NULL, ['permission']);
    $this->decoratedActionsDeterminerMock->expects(static::once())->method('getActions')
      ->with($applicationProcessBundle, [])
      ->willReturn(['bar']);
    static::assertSame(['bar'], $this->actionsDeterminer->getActions($applicationProcessBundle, []));
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
