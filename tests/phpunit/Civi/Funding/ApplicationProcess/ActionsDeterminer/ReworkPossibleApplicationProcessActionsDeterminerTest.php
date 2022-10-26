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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ActionsDeterminer\ReworkPossibleApplicationProcessActionsDeterminer
 */
final class ReworkPossibleApplicationProcessActionsDeterminerTest extends TestCase {

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'approved' => [
      'application_request_rework' => ['request-rework'],
      'application_apply' => [],
      'review_calculative' => [],
      'review_content' => [],
    ],
    'rework-requested' => [
      'application_request_rework' => ['withdraw-rework-request'],
      'application_apply' => [],
      'review_calculative' => ['approve-rework-request', 'reject-rework-request'],
      'review_content' => ['approve-rework-request', 'reject-rework-request'],
    ],
    'rework' => [
      'application_request_rework' => [],
      'application_apply' => ['apply'],
      'review_calculative' => [],
      'review_content' => [],
    ],
    'rework-review-requested' => [
      'application_request_rework' => ['request-rework'],
      'application_apply' => [],
      'review_calculative' => ['review'],
      'review_content' => ['review'],
    ],
    'rework-review' => [
      'application_request_rework' => [],
      'application_apply' => [],
      'review_calculative' => ['approve-calculative', 'reject-calculative'],
      'review_content' => ['approve-content', 'reject-content'],
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
    $this->decoratedActionsDeterminerMock->expects(static::atLeastOnce())->method('getActions')->willReturn([]);
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

  public function testGetActionsDecorated(): void {
    $this->decoratedActionsDeterminerMock->expects(static::once())->method('getActions')
      ->with('foo', ['permission'])
      ->willReturn(['bar']);
    static::assertSame(['bar'], $this->actionsDeterminer->getActions('foo', ['permission']));
  }

}
