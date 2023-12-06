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

namespace Civi\Funding\ClearingProcess;

use Civi\Api4\FundingClearingProcess;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\Event\ClearingProcess\ClearingProcessCreatedEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessPreCreateEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessPreUpdateEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\ClearingProcess\ClearingProcessManager
 * @covers \Civi\Funding\Event\ClearingProcess\ClearingProcessPreCreateEvent
 * @covers \Civi\Funding\Event\ClearingProcess\ClearingProcessCreatedEvent
 * @covers \Civi\Funding\Event\ClearingProcess\ClearingProcessPreUpdateEvent
 * @covers \Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent
 */
final class ClearingProcessManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ClearingProcessManager $clearingProcessManager;

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::register(ClearingProcessFactory::class);
    ClockMock::withClockMock(1234567);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->clearingProcessManager = new ClearingProcessManager(
      $this->api4Mock,
      $this->eventDispatcherMock
    );
  }

  public function testCreate(): void {
    $fundingCase = FundingCaseFactory::createFundingCase([
      'status' => 'draft',
      'report_data' => [],
    ]);
    $clearingProcess = ClearingProcessFactory::create(['id' => NULL]);

    $expectedDispatchCalls = [
      [
        ClearingProcessPreCreateEvent::class,
        new ClearingProcessPreCreateEvent($clearingProcess, $fundingCase),
      ],
      [
        ClearingProcessCreatedEvent::class,
        new ClearingProcessCreatedEvent($clearingProcess, $fundingCase),
      ],
    ];
    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')
      ->willReturnCallback(function (...$args) use (&$expectedDispatchCalls) {
        static::assertEquals(array_shift($expectedDispatchCalls), $args);

        return $args[1];
      });

    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingClearingProcess::getEntityName(), $clearingProcess->toArray())
      ->willReturn(new Result([$clearingProcess->toArray()]));

    static::assertEquals($clearingProcess, $this->clearingProcessManager->create($fundingCase));
  }

  public function testGet(): void {
    $clearingProcess = ClearingProcessFactory::create();

    $this->api4Mock->method('getEntity')
      ->with(FundingClearingProcess::getEntityName(), $clearingProcess->getId())
      ->willReturn(NULL, $clearingProcess->toArray());

    static::assertNull($this->clearingProcessManager->get($clearingProcess->getId()));
    static::assertEquals($clearingProcess, $this->clearingProcessManager->get($clearingProcess->getId()));
  }

  public function testGetFirstByFundingCaseId(): void {
    $clearingProcess = ClearingProcessFactory::create();

    $this->api4Mock->method('getEntities')->with(
      FundingClearingProcess::getEntityName(),
      Comparison::new('funding_case_id', '=', $clearingProcess->getFundingCaseId()),
      ['id' => 'ASC'],
      1
    )->willReturn(new Result([]), new Result([$clearingProcess->toArray()]));

    static::assertNull($this->clearingProcessManager->getFirstByFundingCaseId($clearingProcess->getFundingCaseId()));
    static::assertEquals(
      $clearingProcess,
      $this->clearingProcessManager->getFirstByFundingCaseId($clearingProcess->getFundingCaseId())
    );
  }

  public function testUpdate(): void {
    $previousClearingProcess = ClearingProcessFactory::create();
    $clearingProcess = clone $previousClearingProcess;
    $clearingProcess->setStatus('new_status');
    sleep(1);

    $this->api4Mock->method('getEntity')
      ->with(FundingClearingProcess::getEntityName(), $clearingProcess->getId())
      ->willReturn($previousClearingProcess->toArray());

    $expectedDispatchCalls = [
      [
        ClearingProcessPreUpdateEvent::class,
        new ClearingProcessPreUpdateEvent($previousClearingProcess, $clearingProcess),
      ],
      [
        ClearingProcessUpdatedEvent::class,
        new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcess),
      ],
    ];
    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')->willReturnCallback(
      function (...$args) use (&$expectedDispatchCalls) {
        static::assertEquals(array_shift($expectedDispatchCalls), $args);

        return $args[1];
      }
    );

    $this->api4Mock->expects(static::once())->method('updateEntity')->with(
      FundingClearingProcess::getEntityName(),
      $clearingProcess->getId(),
      ['modification_date' => date('Y-m-d H:i:s')] + $clearingProcess->toArray()
    );

    $this->clearingProcessManager->update($clearingProcess);
    static::assertSame(time(), $clearingProcess->getModificationDate()->getTimestamp());
  }

}
