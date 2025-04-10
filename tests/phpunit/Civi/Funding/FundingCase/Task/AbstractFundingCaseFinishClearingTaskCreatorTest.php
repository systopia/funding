<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Task;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\FundingCase\Task\Traits\FundingCaseFinishClearingTaskTestTrait;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Task\AbstractFundingCaseFinishClearingTaskCreator
 */
final class AbstractFundingCaseFinishClearingTaskCreatorTest extends TestCase {

  use FundingCaseFinishClearingTaskTestTrait;

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private AbstractFundingCaseFinishClearingTaskCreator $taskCreator;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->taskCreator = $this->getMockForAbstractClass(
      AbstractFundingCaseFinishClearingTaskCreator::class,
      [$this->api4Mock]
    );
  }

  /**
   * @dataProvider provideFinishedStatus
   */
  public function testCreateTasksOnChangeApplicationWithUndecidedEligibility(string $finishedStatus): void {
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      ['status' => $finishedStatus],
      [],
      ['status' => 'ongoing']
    );

    $this->api4Mock->expects(static::once())->method('countEntities')
      ->with(FundingApplicationProcess::getEntityName(), CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
        'is_eligible' => NULL,
      ]))->willReturn(1);

    static::assertSame(
      [],
      [...$this->taskCreator->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]
    );
  }

  /**
   * @dataProvider provideFinishedStatus
   */
  public function testCreateTasksOnChangeEligibleApplicationWithoutFinishedClearing(string $finishedStatus): void {
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      ['status' => $finishedStatus],
      [],
      ['status' => 'ongoing']
    );

    $series = $this->createCountEntitiesSeries(
      $clearingProcessBundle->getFundingCase()->getId(),
      0,
      1
    );

    $this->api4Mock->expects(static::exactly(count($series)))->method('countEntities')
      ->willReturnCallback(function (...$args) use (&$series) {
        // @phpstan-ignore offsetAccess.nonArray
        [$expectedArgs, $return] = array_shift($series);
        static::assertEquals($expectedArgs, $args);

        return $return;
      });

    static::assertSame(
      [],
      [...$this->taskCreator->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]
    );
  }

  public function testCreateTasksOnChangeClearingUnfinished(): void {
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review-requested']);
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      ['status' => 'review'],
      [],
      ['status' => 'ongoing']
    );

    $this->api4Mock->expects(static::never())->method('countEntities');

    static::assertSame(
      [],
      [...$this->taskCreator->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]
    );
  }

  /**
   * @dataProvider provideFinishedStatus
   */
  public function testCreateTasksOnChange(string $finishedStatus): void {
    $previousClearingProcess = ClearingProcessFactory::create(['status' => 'review']);
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      ['status' => $finishedStatus],
      [],
      ['status' => 'ongoing']
    );

    $series = $this->createCountEntitiesSeries(
      $clearingProcessBundle->getFundingCase()->getId(),
      0,
      0
    );

    $this->api4Mock->expects(static::exactly(count($series)))->method('countEntities')
      ->willReturnCallback(function (...$args) use (&$series) {
        // @phpstan-ignore offsetAccess.nonArray
        [$expectedArgs, $return] = array_shift($series);
        static::assertEquals($expectedArgs, $args);

        return $return;
      });

    static::assertEquals([
      FundingTaskEntity::newTask([
        'subject' => 'Finish Clearing',
        'affected_identifier' => $clearingProcessBundle->getFundingCase()->getIdentifier(),
        'required_permissions' => [
          'review_clearing_calculative',
          'review_clearing_content',
        ],
        'type' => 'finish_clearing',
        'funding_case_id' => $clearingProcessBundle->getFundingCase()->getId(),
      ]),
    ], [...$this->taskCreator->createTasksOnChange($clearingProcessBundle, $previousClearingProcess)]);
  }

  /**
   * @phpstan-return iterable<array{string}>
   */
  public function provideFinishedStatus(): iterable {
    yield ['accepted'];
    yield ['rejected'];
  }

}
