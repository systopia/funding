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

namespace Civi\Funding\ApplicationProcess;

use Civi\Api4\FundingApplicationSnapshot;
use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\ApplicationSnapshotFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationSnapshotManager
 */
final class ApplicationSnapshotManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ApplicationSnapshotManager $applicationSnapshotManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->applicationSnapshotManager = new ApplicationSnapshotManager($this->api4Mock);
  }

  public function testAdd(): void {
    $applicationSnapshot = ApplicationSnapshotFactory::createApplicationSnapshot();

    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingApplicationSnapshot::getEntityName(), $applicationSnapshot->toArray())
      ->willReturn(new Result([['id' => 135] + $applicationSnapshot->toArray()]));

    $this->applicationSnapshotManager->add($applicationSnapshot);
    static::assertSame(135, $applicationSnapshot->getId());
  }

  public function testGetByApplicationProcessId(): void {
    $applicationSnapshot = ApplicationSnapshotFactory::createApplicationSnapshot();

    $this->api4Mock->method('getEntities')->with(
      FundingApplicationSnapshot::getEntityName(),
      Comparison::new('application_process_id', '=', 12),
      ['id' => 'DESC'],
    )->willReturn(new Result([$applicationSnapshot->toArray()]));

    static::assertEquals([$applicationSnapshot], $this->applicationSnapshotManager->getByApplicationProcessId(12));
  }

  public function testGetLastByApplicationProcessId(): void {
    $applicationSnapshot = ApplicationSnapshotFactory::createApplicationSnapshot();

    $this->api4Mock->method('getEntities')->with(
      FundingApplicationSnapshot::getEntityName(),
      Comparison::new('application_process_id', '=', 12),
      ['id' => 'DESC'],
      1,
    )->willReturn(new Result([$applicationSnapshot->toArray()]));

    static::assertEquals($applicationSnapshot, $this->applicationSnapshotManager->getLastByApplicationProcessId(12));
  }

  public function testGetLastByApplicationProcessIdNotFound(): void {
    $applicationSnapshot = ApplicationSnapshotFactory::createApplicationSnapshot();

    $this->api4Mock->method('getEntities')->with(
      FundingApplicationSnapshot::getEntityName(),
      Comparison::new('application_process_id', '=', 12),
      ['id' => 'DESC'],
      1,
    )->willReturn(new Result([]));

    static::assertNull($this->applicationSnapshotManager->getLastByApplicationProcessId(12));
  }

}
