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

namespace Civi\Funding\EventSubscriber\PayoutProcess;

use Civi\Funding\EntityFactory\DrawdownBundleFactory;
use Civi\Funding\Event\PayoutProcess\DrawdownAcceptedEvent;
use Civi\Funding\PayoutProcess\DrawdownDocumentCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\PayoutProcess\DrawdownDocumentSubscriber
 */
final class DrawdownDocumentSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\PayoutProcess\DrawdownDocumentCreator&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $drawdownDocumentCreatorMock;

  private DrawdownDocumentSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->drawdownDocumentCreatorMock = $this->createMock(DrawdownDocumentCreator::class);
    $this->subscriber = new DrawdownDocumentSubscriber($this->drawdownDocumentCreatorMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      DrawdownAcceptedEvent::class => 'onAccepted',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnAccepted(): void {
    $drawdownBundle = DrawdownBundleFactory::create(['status' => 'accepted']);

    $this->drawdownDocumentCreatorMock->expects(static::once())->method('createDrawdownDocument')
      ->with($drawdownBundle->getDrawdown());
    $this->subscriber->onAccepted(new DrawdownAcceptedEvent($drawdownBundle));
  }

}
