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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ActionsDeterminer\ReworkPossibleApplicationProcessActionsDeterminer
 */
final class ReworkPossibleApplicationProcessActionsDeterminerTest extends TestCase {

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'approved' => [
      'application_modify' => [],
      'application_request_rework' => ['request-rework'],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['update'],
      'review_content' => ['update'],
    ],
    'rework-requested' => [
      'application_modify' => [],
      'application_request_rework' => ['withdraw-rework-request'],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['approve-rework-request', 'reject-rework-request', 'update'],
      'review_content' => ['approve-rework-request', 'reject-rework-request', 'update'],
    ],
    'rework' => [
      'application_modify' => ['save'],
      'application_request_rework' => [],
      'application_apply' => ['apply'],
      'application_withdraw' => ['withdraw-change'],
      'review_calculative' => ['update'],
      'review_content' => ['update'],
    ],
    'rework-review-requested' => [
      'application_modify' => ['request-rework'],
      'application_request_rework' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['review', 'update'],
      'review_content' => ['review', 'update'],
    ],
    'rework-review' => [
      'application_modify' => [],
      'application_request_rework' => [],
      'application_apply' => [],
      'application_withdraw' => [],
      'review_calculative' => ['set-calculative-review-result', 'request-change', 'update'],
      'review_content' => ['set-content-review-result', 'request-change', 'update'],
    ],
  ];

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
    $this->decoratedActionsDeterminerMock->expects(static::atLeastOnce())->method('getActions')->willReturn([]);
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
      $fullStatus = new FullApplicationProcessStatus('approved', TRUE, TRUE);
      static::assertSame(['update'], $this->actionsDeterminer->getActions($fullStatus, [$permission]));

      $actions = self::STATUS_PERMISSION_ACTIONS_MAP['rework-review'][$permission];
      $fullStatus = new FullApplicationProcessStatus('rework-review', TRUE, NULL);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('rework-review', NULL, TRUE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));

      $actions[] = 'approve-change';
      $fullStatus = new FullApplicationProcessStatus('rework-review', TRUE, TRUE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
    }
  }

  public function testGetActionsReject(): void {
    foreach (['review_calculative', 'review_content'] as $permission) {
      $fullStatus = new FullApplicationProcessStatus('approved', FALSE, FALSE);
      static::assertSame(['update'], $this->actionsDeterminer->getActions($fullStatus, [$permission]));

      $actions = self::STATUS_PERMISSION_ACTIONS_MAP['rework-review'][$permission];
      $fullStatus = new FullApplicationProcessStatus('rework-review', NULL, NULL);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));

      $actions[] = 'reject-change';
      $fullStatus = new FullApplicationProcessStatus('rework-review', FALSE, FALSE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('rework-review', NULL, FALSE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('rework-review', TRUE, FALSE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('rework-review', FALSE, TRUE);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
      $fullStatus = new FullApplicationProcessStatus('rework-review', FALSE, NULL);
      static::assertEquals($actions, $this->actionsDeterminer->getActions($fullStatus, [$permission]));
    }
  }

  public function testGetActionsDecorated(): void {
    $fullStatus = new FullApplicationProcessStatus('foo', NULL, NULL);
    $this->decoratedActionsDeterminerMock->expects(static::once())->method('getActions')
      ->with($fullStatus, ['permission'])
      ->willReturn(['bar']);
    static::assertSame(['bar'], $this->actionsDeterminer->getActions($fullStatus, ['permission']));
  }

}
