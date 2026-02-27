<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessAmountEligibleSubscriber
 */
final class ApplicationProcessAmountEligibleSubscriberTest extends TestCase {

  private ApplicationProcessAmountEligibleSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->subscriber = new ApplicationProcessAmountEligibleSubscriber();
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreCreateEvent::class => ['onPreCreate', -101],
      ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', -101],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnPreCreateEligible(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'amount_requested' => 1.2,
      'is_eligible' => TRUE,
    ]);

    $this->subscriber->onPreCreate(new ApplicationProcessPreCreateEvent($applicationProcessBundle));
    static::assertSame(1.2, $applicationProcessBundle->getApplicationProcess()->getAmountEligible());
  }

  public function testOnPreUpdateEligible(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'amount_requested' => 1.2,
      'amount_eligible' => 100.0,
      'is_eligible' => TRUE,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'amount_requested' => 200.0,
      'amount_eligible' => 100.0,
      'is_eligible' => NULL,
    ]);

    $this->subscriber->onPreUpdate(
      new ApplicationProcessPreUpdateEvent($previousApplicationProcess, $applicationProcessBundle)
    );
    static::assertSame(1.2, $applicationProcessBundle->getApplicationProcess()->getAmountEligible());
  }

  public function testOnPreUpdateUneligible(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'amount_requested' => 100.0,
      'amount_eligible' => 100.0,
      'is_eligible' => FALSE,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'amount_requested' => 100.0,
      'amount_eligible' => 100.0,
      'is_eligible' => TRUE,
    ]);

    $this->subscriber->onPreUpdate(
      new ApplicationProcessPreUpdateEvent($previousApplicationProcess, $applicationProcessBundle)
    );
    static::assertSame(0.0, $applicationProcessBundle->getApplicationProcess()->getAmountEligible());
  }

  public function testOnPreUpdateEligibilityUnknown(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'amount_requested' => 300.0,
      'amount_eligible' => 100.0,
      'is_eligible' => NULL,
    ]);
    $previousApplicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'amount_requested' => 200.0,
      'amount_eligible' => 100.0,
      'is_eligible' => TRUE,
    ]);

    $this->subscriber->onPreUpdate(
      new ApplicationProcessPreUpdateEvent($previousApplicationProcess, $applicationProcessBundle)
    );
    // Amount eligible is unchanged.
    static::assertSame(100.0, $applicationProcessBundle->getApplicationProcess()->getAmountEligible());
  }

}
