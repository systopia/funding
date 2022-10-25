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

namespace Civi\RemoteTools\EventSubscriber;

use Civi\API\Event\PrepareEvent;
use Civi\API\Kernel;
use Civi\API\Provider\ProviderInterface;
use Civi\RemoteTools\Database\TransactionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\EventSubscriber\AbstractTransactionalApiRequestSubscriber
 */
final class AbstractTransactionalApiRequestSubscriberTest extends TestCase {

  /**
   * @var \Civi\API\Provider\ProviderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $apiProviderMock;

  /**
   * @var \Civi\API\Kernel&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $kernelMock;

  private bool $transactional = FALSE;

  /**
   * @var \CRM_Core_Transaction&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $transactionMock;

  private AbstractTransactionalApiRequestSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->apiProviderMock = $this->createMock(ProviderInterface::class);
    $this->kernelMock = $this->createMock(Kernel::class);
    $transactionFactoryMock = $this->createMock(TransactionFactory::class);
    $this->transactionMock = $this->createMock(\CRM_Core_Transaction::class);
    $transactionFactoryMock->expects(static::atMost(1))->method('createTransaction')
      ->willReturn($this->transactionMock);

    $this->subscriber = new class ($transactionFactoryMock, $this->transactional)
      extends AbstractTransactionalApiRequestSubscriber {

      private bool $transactional;

      public function __construct(TransactionFactory $transactionFactory, bool &$transactional) {
        parent::__construct($transactionFactory);
        $this->transactional = &$transactional;
      }

      protected function isTransactionalAction(string $entity, string $action) : bool {
        return $this->transactional;
      }

    };
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      'civi.api.prepare' => ['onApiPrepare', PHP_INT_MAX],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnApiPrepareNonTransactional(): void {
    $this->transactional = FALSE;
    $request = ['entity' => 'test', 'action' => 'create'];
    $event = new PrepareEvent($this->apiProviderMock, $request, $this->kernelMock);
    $this->subscriber->onApiPrepare($event);

    static::assertSame($this->apiProviderMock, $event->getApiProvider());
  }

  public function testOnApiPrepareTransactionalSuccess(): void {
    $this->transactional = TRUE;
    $request = ['entity' => 'test', 'action' => 'create'];
    $event = new PrepareEvent($this->apiProviderMock, $request, $this->kernelMock);
    $this->subscriber->onApiPrepare($event);

    $result = ['result'];
    $this->apiProviderMock->expects(static::once())->method('invoke')
      ->with($request)
      ->willReturn($result);

    $this->transactionMock->expects(static::once())->method('commit');
    static::assertSame($result, $event->getApiProvider()->invoke($request));
  }

  public function testOnApiPrepareTransactionalError(): void {
    $this->transactional = TRUE;
    $request = ['entity' => 'test', 'action' => 'create'];
    $event = new PrepareEvent($this->apiProviderMock, $request, $this->kernelMock);
    $this->subscriber->onApiPrepare($event);

    $exception = new \RuntimeException('test');
    $this->apiProviderMock->expects(static::once())->method('invoke')
      ->with($request)
      ->willThrowException($exception);

    $this->transactionMock->expects(static::once())->method('rollback')->willReturnSelf();
    $this->transactionMock->expects(static::once())->method('commit');
    static::expectExceptionObject($exception);
    $event->getApiProvider()->invoke($request);
  }

}
