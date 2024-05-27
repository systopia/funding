<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
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
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $clearingProcess = ClearingProcessFactory::create([
      'id' => NULL,
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
    ]);

    $expectedDispatchCalls = [
      [
        ClearingProcessPreCreateEvent::class,
        new ClearingProcessPreCreateEvent($clearingProcess, $applicationProcessBundle),
      ],
      [
        ClearingProcessCreatedEvent::class,
        new ClearingProcessCreatedEvent($clearingProcess, $applicationProcessBundle),
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

    static::assertEquals($clearingProcess, $this->clearingProcessManager->create($applicationProcessBundle));
  }

  public function testGet(): void {
    $clearingProcess = ClearingProcessFactory::create();

    $this->api4Mock->method('getEntity')
      ->with(FundingClearingProcess::getEntityName(), $clearingProcess->getId())
      ->willReturn(NULL, $clearingProcess->toArray());

    static::assertNull($this->clearingProcessManager->get($clearingProcess->getId()));
    static::assertEquals($clearingProcess, $this->clearingProcessManager->get($clearingProcess->getId()));
  }

  public function testGetByApplicationProcessId(): void {
    $clearingProcess = ClearingProcessFactory::create();
    $applicationProcessId = $clearingProcess->getApplicationProcessId();

    $this->api4Mock->method('getEntities')->with(
      FundingClearingProcess::getEntityName(),
      Comparison::new('application_process_id', '=', $applicationProcessId),
    )->willReturn(new Result([]), new Result([$clearingProcess->toArray()]));

    static::assertNull($this->clearingProcessManager->getByApplicationProcessId($applicationProcessId));
    static::assertEquals(
      $clearingProcess,
      $this->clearingProcessManager->getByApplicationProcessId($applicationProcessId)
    );
  }

  public function testUpdate(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $previousClearingProcess = clone $clearingProcessBundle->getClearingProcess();
    $clearingProcess = $clearingProcessBundle->getClearingProcess();
    $clearingProcess->setStatus('new_status');
    sleep(1);

    $this->api4Mock->method('getEntity')
      ->with(FundingClearingProcess::getEntityName(), $clearingProcess->getId())
      ->willReturn($previousClearingProcess->toArray());

    $expectedDispatchCalls = [
      [
        ClearingProcessPreUpdateEvent::class,
        new ClearingProcessPreUpdateEvent($previousClearingProcess, $clearingProcessBundle),
      ],
      [
        ClearingProcessUpdatedEvent::class,
        new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcessBundle),
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

    $this->clearingProcessManager->update($clearingProcessBundle);
    static::assertSame(time(), $clearingProcess->getModificationDate()->getTimestamp());
  }

}
