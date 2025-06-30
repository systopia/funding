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

namespace Civi\Funding\FundingCase\Actions;

use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\FundingCaseType\MetaData\DefaultApplicationProcessStatuses;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Actions\DefaultFundingCaseActionsDeterminer
 * @covers \Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminer
 */
final class DefaultFundingCaseActionsDeterminerTest extends TestCase {

  private const STATUS_PERMISSION_ACTIONS_MAP = [
    'open' => [
      'review_calculative' => ['approve', 'set-notification-contacts'],
      'review_content' => ['approve', 'set-notification-contacts'],
      'review_clearing_calculative' => ['set-notification-contacts'],
      'review_clearing_content' => ['set-notification-contacts'],
      'review_drawdown' => ['set-notification-contacts'],
      'review_case_finish' => [],
    ],
    'ongoing' => [
      'review_calculative' => ['recreate-transfer-contract', 'update-amount-approved', 'set-notification-contacts'],
      'review_content' => ['recreate-transfer-contract', 'update-amount-approved', 'set-notification-contacts'],
      'review_clearing_calculative' => ['set-notification-contacts'],
      'review_clearing_content' => ['set-notification-contacts'],
      'review_drawdown' => ['set-notification-contacts'],
      'review_case_finish' => ['set-notification-contacts'],
    ],
    'cleared' => [
      'review_calculative' => ['set-notification-contacts'],
      'review_content' => ['set-notification-contacts'],
      'review_clearing_calculative' => ['set-notification-contacts'],
      'review_clearing_content' => ['set-notification-contacts'],
      'review_drawdown' => ['set-notification-contacts'],
      'review_case_finish' => ['set-notification-contacts'],
    ],
    'rejected' => [
      'review_calculative' => ['set-notification-contacts'],
      'review_content' => ['set-notification-contacts'],
      'review_clearing_calculative' => ['set-notification-contacts'],
      'review_clearing_content' => ['set-notification-contacts'],
      'review_drawdown' => ['set-notification-contacts'],
      'review_case_finish' => ['set-notification-contacts'],
    ],
    'withdrawn' => [
      'review_calculative' => ['set-notification-contacts'],
      'review_content' => ['set-notification-contacts'],
      'review_clearing_calculative' => ['set-notification-contacts'],
      'review_clearing_content' => ['set-notification-contacts'],
      'review_drawdown' => ['set-notification-contacts'],
      'review_case_finish' => ['set-notification-contacts'],
    ],
  ];

  private DefaultFundingCaseActionsDeterminer $actionsDeterminer;

  /**
   * @var \Civi\Funding\ClearingProcess\ClearingProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $clearingProcessManagerMock;

  /**
   * @phpstan-var array<int, FullApplicationProcessStatus>
   */
  private array $statusList;

  protected function setUp(): void {
    parent::setUp();
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $metaDataMock = new FundingCaseTypeMetaDataMock();
    $this->actionsDeterminer = new DefaultFundingCaseActionsDeterminer(
      $this->clearingProcessManagerMock,
      $metaDataMock
    );
    $this->statusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];

    $metaDataMock->applicationProcessStatuses = DefaultApplicationProcessStatuses::getAll();
  }

  public function testGetActions(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      foreach ($permissionActionsMap as $permission => $actions) {
        static::assertSame(
          $actions,
          $this->actionsDeterminer->getActions($status, $this->statusList, [$permission]),
          sprintf('Status: %s, permission: %s', $status, $permission)
        );
      }
    }
  }

  public function testGetActionsFinishClearing(): void {
    $statusList = [22 => new FullApplicationProcessStatus('review', TRUE, TRUE)];
    static::assertNotContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      'ongoing',
      $statusList,
      [FundingCasePermissions::REVIEW_FINISH]
    ));

    $statusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    static::assertNotContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      'ongoing',
      $statusList,
      [FundingCasePermissions::REVIEW_FINISH]
    ));

    $clearingProcess = ClearingProcessFactory::create(['status' => 'accepted']);
    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with(22)->willReturn($clearingProcess);
    static::assertContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      'ongoing',
      $statusList,
      [FundingCasePermissions::REVIEW_FINISH]
    ));

    $clearingProcess->setValues(['status' => 'rejected'] + $clearingProcess->toArray());
    static::assertContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      'ongoing',
      $statusList,
      [FundingCasePermissions::REVIEW_FINISH]
    ));

    $clearingProcess->setValues(['status' => 'review'] + $clearingProcess->toArray());
    static::assertNotContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      'ongoing',
      $statusList,
      [FundingCasePermissions::REVIEW_FINISH]
    ));
  }

  public function testGetActionsFinishClearingWithoutPermission(): void {
    $statusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    $clearingProcess = ClearingProcessFactory::create(['status' => 'accepted']);
    $this->clearingProcessManagerMock->expects(static::never())->method('getByApplicationProcessId');
    static::assertNotContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      'ongoing',
      $statusList,
      ['review_content']
    ));
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideClearingReviewPermission(): iterable {
    yield [ClearingProcessPermissions::REVIEW_CALCULATIVE];
    yield [ClearingProcessPermissions::REVIEW_CONTENT];
  }

  public function testGetActionsAll(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      $actions = array_values(array_unique(array_merge(...array_values($permissionActionsMap))));
      $permissions = array_keys($permissionActionsMap);
      static::assertEquals(
        $actions,
        $this->actionsDeterminer->getActions($status, $this->statusList, $permissions),
        sprintf('Status: %s, permissions: %s', $status, var_export($permissions, TRUE))
      );
    }
  }

  public function testIsActionAllowed(): void {
    static::assertTrue($this->actionsDeterminer->isActionAllowed(
      'approve',
      'open',
      $this->statusList,
      ['review_calculative']
    ));
    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'some_action',
      'open',
      $this->statusList,
      ['review_calculative']
    ));
    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'approve',
      'ongoing',
      $this->statusList,
      ['review_calculative']
    ));
  }

  public function testIsAnyActionAllowed(): void {
    static::assertTrue(
      $this->actionsDeterminer->isAnyActionAllowed(
        ['some_action', 'approve'],
        'open',
        $this->statusList,
        ['review_calculative'])
    );
    static::assertFalse(
      $this->actionsDeterminer->isAnyActionAllowed(
        ['some_action', 'another_action'],
        'open',
        $this->statusList,
        ['review_calculative'])
    );
    static::assertFalse(
      $this->actionsDeterminer->isAnyActionAllowed(
        ['some_action', 'approve'],
        'ongoing',
        $this->statusList,
        ['review_calculative'])
    );
  }

  public function testApprove(): void {
    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'approve',
      'open',
      [22 => new FullApplicationProcessStatus('review', TRUE, TRUE)],
      ['review_calculative']
    ));

    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'approve',
      'open',
      [
        22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE),
        23 => new FullApplicationProcessStatus('review', TRUE, TRUE),
      ],
      ['review_calculative']
    ));

    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'approve',
      'open',
      [],
      ['review_calculative']
    ));

    static::assertTrue($this->actionsDeterminer->isActionAllowed(
      'approve',
      'open',
      [
        22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE),
        23 => new FullApplicationProcessStatus('withdrawn', TRUE, TRUE),
      ],
      ['review_calculative']
    ));
  }

}
