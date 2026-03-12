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

use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
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

  private ApplicationProcessStatusDeterminerInterface&MockObject $applicationProcessStatusDeterminerMock;

  private ClearingProcessManager&MockObject $clearingProcessManagerMock;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  /**
   * @phpstan-var array<int, FullApplicationProcessStatus>
   */
  private array $statusList;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessStatusDeterminerMock =
      $this->createMock(ApplicationProcessStatusDeterminerInterface::class);
    $this->clearingProcessManagerMock = $this->createMock(ClearingProcessManager::class);
    $this->metaDataMock = new FundingCaseTypeMetaDataMock();
    $this->actionsDeterminer = new DefaultFundingCaseActionsDeterminer(
      $this->applicationProcessStatusDeterminerMock,
      $this->clearingProcessManagerMock,
      $this->metaDataMock
    );
    $this->statusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];

    $this->metaDataMock->applicationProcessStatuses = DefaultApplicationProcessStatuses::getAll();
  }

  public function testGetActions(): void {
    foreach (self::STATUS_PERMISSION_ACTIONS_MAP as $status => $permissionActionsMap) {
      foreach ($permissionActionsMap as $permission => $actions) {
        static::assertSame(
          $actions,
          $this->actionsDeterminer->getActions(
            FundingCaseBundleFactory::create(['status' => $status, 'permissions' => [$permission]]),
            $this->statusList
          ),
          sprintf('Status: %s, permission: %s', $status, $permission)
        );
      }
    }
  }

  public function testGetActionsFinishClearing(): void {
    $statusList = [22 => new FullApplicationProcessStatus('review', TRUE, TRUE)];
    static::assertNotContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'ongoing',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));

    $statusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    static::assertNotContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'ongoing',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));

    $clearingProcess = ClearingProcessFactory::create(['status' => 'accepted']);
    $this->clearingProcessManagerMock->method('getByApplicationProcessId')
      ->with(22)->willReturn($clearingProcess);
    static::assertContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'ongoing',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));

    $clearingProcess->setValues(['status' => 'rejected'] + $clearingProcess->toArray());
    static::assertContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'ongoing',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));

    $clearingProcess->setValues(['status' => 'review'] + $clearingProcess->toArray());
    static::assertNotContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'ongoing',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));
  }

  public function testGetActionsFinishClearingWithoutPermission(): void {
    $statusList = [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)];
    $clearingProcess = ClearingProcessFactory::create(['status' => 'accepted']);
    $this->clearingProcessManagerMock->expects(static::never())->method('getByApplicationProcessId');
    static::assertNotContains(FundingCaseActions::FINISH_CLEARING, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'ongoing',
        'permissions' => ['review_content'],
      ]),
      $statusList,
    ));
  }

  public function testGetActionsReject(): void {
    $statusList = [22 => new FullApplicationProcessStatus('test', TRUE, TRUE)];

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'test',
      'eligible' => TRUE,
    ]));
    static::assertNotContains(FundingCaseActions::REJECT, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'open',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'test',
      'eligible' => FALSE,
    ]));
    static::assertContains(FundingCaseActions::REJECT, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'open',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));
    static::assertNotContains(FundingCaseActions::REJECT, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'ongoing',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'test',
      'eligible' => NULL,
    ]));
    $this->applicationProcessStatusDeterminerMock->expects(static::exactly(2))->method('getStatus')
      ->with($statusList[22], 'reject')
      ->willReturnCallback(function () {
        static $called = FALSE;

        if (!$called) {
          $called = TRUE;

          throw new \InvalidArgumentException();
        }

        return new FullApplicationProcessStatus('newStatus', FALSE, FALSE);
      });
    static::assertNotContains(FundingCaseActions::REJECT, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'open',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));
    static::assertContains(FundingCaseActions::REJECT, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'open',
        'permissions' => [FundingCasePermissions::REVIEW_FINISH],
      ]),
      $statusList,
    ));
  }

  public function testGetActionsRejectWithoutPermission(): void {
    $statusList = [22 => new FullApplicationProcessStatus('test', TRUE, TRUE)];

    $this->metaDataMock->addApplicationProcessStatus(new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'test',
      'is_eligible' => FALSE,
    ]));
    static::assertNotContains(FundingCaseActions::REJECT, $this->actionsDeterminer->getActions(
      FundingCaseBundleFactory::create([
        'status' => 'open',
        'permissions' => ['review_content'],
      ]),
      $statusList,
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
        $this->actionsDeterminer->getActions(
          FundingCaseBundleFactory::create(['status' => $status, 'permissions' => $permissions]),
          $this->statusList
        ),
        sprintf('Status: %s, permissions: %s', $status, var_export($permissions, TRUE))
      );
    }
  }

  public function testIsActionAllowed(): void {
    static::assertTrue($this->actionsDeterminer->isActionAllowed(
      'approve',
      FundingCaseBundleFactory::create(['status' => 'open', 'permissions' => ['review_calculative']]),
      $this->statusList,
    ));
    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'some_action',
      FundingCaseBundleFactory::create(['status' => 'open', 'permissions' => ['review_calculative']]),
      $this->statusList,
    ));
    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'approve',
      FundingCaseBundleFactory::create(['status' => 'ongoing', 'permissions' => ['review_calculative']]),
      $this->statusList,
    ));
  }

  public function testApprove(): void {
    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'approve',
      FundingCaseBundleFactory::create(['status' => 'open', 'permissions' => ['review_calculative']]),
      [22 => new FullApplicationProcessStatus('review', TRUE, TRUE)],
    ));

    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'approve',
      FundingCaseBundleFactory::create(['status' => 'open', 'permissions' => ['review_calculative']]),
      [
        22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE),
        23 => new FullApplicationProcessStatus('review', TRUE, TRUE),
      ],
    ));

    static::assertFalse($this->actionsDeterminer->isActionAllowed(
      'approve',
      FundingCaseBundleFactory::create(['status' => 'open', 'permissions' => ['review_calculative']]),
      [],
    ));

    static::assertTrue($this->actionsDeterminer->isActionAllowed(
      'approve',
      FundingCaseBundleFactory::create(['status' => 'open', 'permissions' => ['review_calculative']]),
      [
        22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE),
        23 => new FullApplicationProcessStatus('withdrawn', TRUE, TRUE),
      ],
    ));
  }

}
