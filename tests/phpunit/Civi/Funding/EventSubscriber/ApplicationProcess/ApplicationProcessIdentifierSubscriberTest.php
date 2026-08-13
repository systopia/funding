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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Api4\FundingTask;
use Civi\Api4\Generic\DAOUpdateAction;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGeneratorInterface;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessIdentifierSubscriber
 */
final class ApplicationProcessIdentifierSubscriberTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationIdentifierGeneratorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationIdentifierGeneratorMock;

  /**
   * @var \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessIdentifierSubscriber
   */
  private ApplicationProcessIdentifierSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->applicationIdentifierGeneratorMock = $this->createMock(ApplicationIdentifierGeneratorInterface::class);
    $this->subscriber = new ApplicationProcessIdentifierSubscriber(
      $this->api4Mock,
      $this->applicationIdentifierGeneratorMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessCreatedEvent::class => ['onCreated', PHP_INT_MAX],
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      if (is_array($method)) {
        [$method, $priority] = $method;
      }
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnCreated(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create(['id' => 2]);
    $event = new ApplicationProcessCreatedEvent($applicationProcessBundle);

    $this->applicationIdentifierGeneratorMock->method('generateIdentifier')
      ->with($applicationProcessBundle)
      ->willReturn('generated');

    $this->api4Mock->expects(static::once())->method('executeAction')
      ->with(static::callback(function (DAOUpdateAction $action) {
        static::assertSame([['id', '=', 2]], $action->getWhere());
        static::assertSame('generated', $action->getValue('identifier'));

        return TRUE;
      }));

    $this->subscriber->onCreated($event);
    static::assertSame('generated', $applicationProcessBundle->getApplicationProcess()->getIdentifier());
  }

  public function testOnPreUpdate_WithFundingCaseChange(): void {
    $previousApplicationProcessBundle = ApplicationProcessBundleFactory::create(fundingCaseValues: ['id' => 1]);
    $applicationProcessBundle = ApplicationProcessBundleFactory::create(
      ['identifier' => 'identifierX'],
      fundingCaseValues: ['id' => 2]
    );

    $this->applicationIdentifierGeneratorMock->method('generateIdentifierOnFundingCaseChange')
      ->with($applicationProcessBundle)
      ->willReturn('generated');

    $this->api4Mock->expects(static::once())->method('executeAction')
      ->with(FundingTask::update(FALSE)
        ->setIgnoreCasePermissions(TRUE)
        ->addValue('funding_case_task.affected_identifier', 'generated')
        ->addWhere('funding_case_task.affected_identifier', '=', 'identifierX')
      );

    $event = new ApplicationProcessPreUpdateEvent($previousApplicationProcessBundle, $applicationProcessBundle);
    $this->subscriber->onPreUpdate($event);
    static::assertSame('generated', $applicationProcessBundle->getApplicationProcess()->getIdentifier());
  }

  public function testOnPreUpdate_WithoutFundingCaseChange(): void {
    $previousApplicationProcessBundle = ApplicationProcessBundleFactory::create();
    $applicationProcessBundle = ApplicationProcessBundleFactory::create(['identifier' => 'identifierX']);

    $this->api4Mock->expects(static::never())->method('executeAction');

    $event = new ApplicationProcessPreUpdateEvent($previousApplicationProcessBundle, $applicationProcessBundle);
    $this->subscriber->onPreUpdate($event);
    static::assertSame('identifierX', $applicationProcessBundle->getApplicationProcess()->getIdentifier());
  }

}
