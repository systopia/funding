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

namespace Civi\Funding\EventSubscriber\ClearingProcess;

use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ClearingProcess\ClearingProcessUpdatedSubscriber
 */
final class ClearingProcessUpdatedSubscriberTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ClearingProcessUpdatedSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->subscriber = new ClearingProcessUpdatedSubscriber($this->api4Mock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ClearingProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnUpdated(): void {
    $clearingProcessEntityBundle = ClearingProcessBundleFactory::create(
      [],
      [
        'title' => 'Title',
        'identifier' => 'Identifier',
      ]
    );
    $previousClearingProcess = ClearingProcessFactory::create();
    $event = new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcessEntityBundle);

    $this->subscriber->onUpdated($event);

    $this->api4Mock->expects(static::never());
  }

  public function testOnUpdatedWithGrunddatenZeitraeumeIncomplete(): void {
    $reportDataSet = [
      [
        'grunddaten' => [
          'zeitraeume' => [
            [
              'beginn' => '2025-01-13',
            ],
          ],
        ],
      ],
      [
        'grunddaten' => [
          'zeitraeume' => [
            [
              'ende' => '2025-01-13',
            ],
          ],
        ],
      ],
      [
        'grunddaten' => [
          'zeitraeume' => [],
        ],
      ],
      [
        'grunddaten' => [
          'zeitraeume' => [
            'beginn' => '',
            'ende' => '',
          ],
        ],
      ],
    ];

    $clearingProcessEntityBundle = ClearingProcessBundleFactory::create(
      [],
      [
        'title' => 'Title',
        'identifier' => 'Identifier',
      ]
    );

    foreach ($reportDataSet as $reportData) {
      $previousClearingProcess = ClearingProcessFactory::create([
        'report_data' => $reportData,
      ]);
      $event = new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcessEntityBundle);

      $this->subscriber->onUpdated($event);

      $this->api4Mock->expects(static::never());
    }
  }

}
