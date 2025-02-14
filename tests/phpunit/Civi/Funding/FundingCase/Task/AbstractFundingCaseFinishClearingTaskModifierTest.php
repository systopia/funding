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

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\Entity\FundingTaskEntity;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\Task\Traits\FundingCaseFinishClearingTaskTestTrait;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Task\AbstractFundingCaseFinishClearingTaskModifier
 */
final class AbstractFundingCaseFinishClearingTaskModifierTest extends TestCase {

  use FundingCaseFinishClearingTaskTestTrait;

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private AbstractFundingCaseFinishClearingTaskModifier $taskModifier;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->taskModifier = $this->getMockForAbstractClass(
      AbstractFundingCaseFinishClearingTaskModifier::class,
      [$this->api4Mock]
    );
  }

  public function testModifyTaskCleared(): void {
    $previousFundingCase = FundingCaseFactory::createFundingCase(['status' => 'ongoing']);
    $fundingCaseBundle = FundingCaseBundleFactory::create(['status' => 'cleared']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Funding Case Clearing',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => [
        'review_clearing_calculative',
        'review_clearing_content',
      ],
      'type' => 'finish_clearing',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $this->api4Mock->expects(static::never())->method('countEntities');

    static::assertTrue($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::COMPLETED, $task->getStatusName());
  }

  public function testModifyTaskNotOngoingNotCleared(): void {
    $previousFundingCase = FundingCaseFactory::createFundingCase(['status' => 'ongoing']);
    $fundingCaseBundle = FundingCaseBundleFactory::create(['status' => 'new']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Funding Case Clearing',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => [
        'review_clearing_calculative',
        'review_clearing_content',
      ],
      'type' => 'finish_clearing',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $this->api4Mock->expects(static::never())->method('countEntities');

    static::assertTrue($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskNotCleared(): void {
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => 'ongoing',
      'modification_date' => '2000-01-01 01:01:01',
    ]);
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => 'ongoing',
      'modification_date' => '2000-01-02 03:04:05',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Funding Case Clearing',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => [
        'review_clearing_calculative',
        'review_clearing_content',
      ],
      'type' => 'finish_clearing',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $series = $this->createCountEntitiesSeries(
      $fundingCaseBundle->getFundingCase()->getId(),
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

    static::assertFalse($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

  public function testModifyTaskEligibleApplicationWithoutFinishedClearing(): void {
    $previousFundingCase = FundingCaseFactory::createFundingCase([
      'status' => 'ongoing',
      'modification_date' => '2000-01-01 01:01:01',
    ]);
    $fundingCaseBundle = FundingCaseBundleFactory::create([
      'status' => 'ongoing',
      'modification_date' => '2000-01-02 03:04:05',
    ]);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Finish Funding Case Clearing',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => [
        'review_clearing_calculative',
        'review_clearing_content',
      ],
      'type' => 'finish_clearing',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    $series = $this->createCountEntitiesSeries(
      $fundingCaseBundle->getFundingCase()->getId(),
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

    static::assertTrue($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::CANCELLED, $task->getStatusName());
  }

  public function testModifyTaskDifferentTaskType(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(['status' => 'ongoing']);
    $previousFundingCase = FundingCaseFactory::createFundingCase(['status' => 'cleared']);

    $task = FundingTaskEntity::newTask([
      'subject' => 'Some Subject',
      'affected_identifier' => $fundingCaseBundle->getFundingCase()->getIdentifier(),
      'required_permissions' => [
        'review_clearing_calculative',
        'review_clearing_content',
      ],
      'type' => 'some_type',
      'funding_case_id' => $fundingCaseBundle->getFundingCase()->getId(),
    ]);

    static::assertFalse($this->taskModifier->modifyTask($task, $fundingCaseBundle, $previousFundingCase));
    static::assertSame(ActivityStatusNames::SCHEDULED, $task->getStatusName());
  }

}
