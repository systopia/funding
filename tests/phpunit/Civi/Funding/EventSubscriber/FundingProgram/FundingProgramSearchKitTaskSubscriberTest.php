<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\FundingProgram;

use Civi\Api4\FundingProgram;
use Civi\Core\Event\GenericHookEvent;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Permissions;

/**
 * @group headless
 *
 * @covers \Civi\Funding\EventSubscriber\FundingProgram\FundingProgramSearchKitTaskSubscriber
 */
final class FundingProgramSearchKitTaskSubscriberTest extends AbstractFundingHeadlessTestCase {

  private FundingProgramSearchKitTaskSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->subscriber = new FundingProgramSearchKitTaskSubscriber();
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      'hook_civicrm_searchKitTasks' => 'onSearchKitTasks',
    ];
    static::assertEquals($expectedSubscriptions, FundingProgramSearchKitTaskSubscriber::getSubscribedEvents());
  }

  public function testOnSearchKitTasksWithoutPermissionCheck(): void {
    $event = GenericHookEvent::create([
      'checkPermissions' => FALSE,
      'tasks' => [FundingProgram::getEntityName() => []],
    ]);

    $this->subscriber->onSearchKitTasks($event);

    static::assertArrayHasKey('clone', $event->tasks[FundingProgram::getEntityName()]);
    static::assertSame('Clone Funding Program', $event->tasks[FundingProgram::getEntityName()]['clone']['title']);
  }

  public function testOnSearchKitTasksWithMissingPermission(): void {
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM]);

    $event = GenericHookEvent::create([
      'checkPermissions' => TRUE,
      'userId' => 1,
      'tasks' => [FundingProgram::getEntityName() => []],
    ]);

    $this->subscriber->onSearchKitTasks($event);

    static::assertArrayNotHasKey('clone', $event->tasks[FundingProgram::getEntityName()]);
  }

  public function testOnSearchKitTasksWithPermission(): void {
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);

    $event = GenericHookEvent::create([
      'checkPermissions' => TRUE,
      'userId' => 1,
      'tasks' => [FundingProgram::getEntityName() => []],
    ]);

    $this->subscriber->onSearchKitTasks($event);

    static::assertArrayHasKey('clone', $event->tasks[FundingProgram::getEntityName()]);
  }

}
