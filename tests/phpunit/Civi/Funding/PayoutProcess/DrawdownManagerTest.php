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

namespace Civi\Funding\PayoutProcess;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Entity\DrawdownBundle;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\PayoutProcessBundleFactory;
use Civi\Funding\Event\PayoutProcess\DrawdownAcceptedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownCreatedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownDeletedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownPreCreateEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownPreUpdateEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownUpdatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\PayoutProcess\DrawdownManager
 */
final class DrawdownManagerTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private DrawdownManager $drawdownManager;

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  /**
   * @var \Civi\Funding\PayoutProcess\PayoutProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $payoutProcessManagerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(12345);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);

    $this->drawdownManager = new DrawdownManager(
      $this->api4Mock,
      $this->eventDispatcherMock,
      $this->payoutProcessManagerMock
    );
  }

  public function testAccept(): void {
    $drawdown = DrawdownFactory::create();
    $previousDrawdown = clone $drawdown;

    $this->api4Mock->expects(static::once())->method('getEntity')
      ->with(FundingDrawdown::getEntityName(), $drawdown->getId())
      ->willReturn($previousDrawdown->toArray());

    $payoutProcessBundle = PayoutProcessBundleFactory::create();
    $this->payoutProcessManagerMock->expects(static::once())->method('getBundle')
      ->with($drawdown->getPayoutProcessId())
      ->willReturn($payoutProcessBundle);

    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(
        FundingDrawdown::getEntityName(),
        $drawdown->getId(),
        [
          'status' => 'accepted',
          'reviewer_contact_id' => 2,
          'acception_date' => date('Y-m-d H:i:s'),
        ] + $drawdown->toArray(),
      );

    $drawdownBundle = new DrawdownBundle($drawdown, $payoutProcessBundle);

    $expectedDispatchCalls = [
      [
        DrawdownPreUpdateEvent::class,
        new DrawdownPreUpdateEvent($previousDrawdown, $drawdownBundle),
      ],
      [
        DrawdownUpdatedEvent::class,
        new DrawdownUpdatedEvent($previousDrawdown, $drawdownBundle),
      ],
      [
        DrawdownAcceptedEvent::class,
        new DrawdownAcceptedEvent($drawdownBundle),
      ],
    ];
    $this->eventDispatcherMock->expects(static::exactly(3))->method('dispatch')
      ->willReturnCallback(function (...$args) use (&$expectedDispatchCalls) {
        static::assertEquals(array_shift($expectedDispatchCalls), $args);

        return $args[1];
      });

    $this->drawdownManager->accept($drawdown, 2);
    static::assertSame('accepted', $drawdown->getStatus());
    static::assertSame(2, $drawdown->getReviewerContactId());
    static::assertEquals(new \DateTime('@12345'), $drawdown->getAcceptionDate());
  }

  public function testCreateNew(): void {
    $payoutProcessBundle = PayoutProcessBundleFactory::create();

    $drawdown = DrawdownEntity::fromArray([
      'payout_process_id' => $payoutProcessBundle->getPayoutProcess()->getId(),
      'status' => 'new',
      'creation_date' => date('Y-m-d H:i:s'),
      'amount' => 12.34,
      'acception_date' => NULL,
      'requester_contact_id' => 56,
      'reviewer_contact_id' => NULL,
    ]);
    $persistedDrawdown = DrawdownEntity::fromArray(['id' => 99] + $drawdown->toArray());

    $expectedDispatchCalls = [
      [
        DrawdownPreCreateEvent::class,
        new DrawdownPreCreateEvent(new DrawdownBundle($drawdown, $payoutProcessBundle)),
      ],
      [
        DrawdownCreatedEvent::class,
        new DrawdownCreatedEvent(new DrawdownBundle($persistedDrawdown, $payoutProcessBundle)),
      ],
    ];
    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')
      ->willReturnCallback(function (...$args) use (&$expectedDispatchCalls) {
        static::assertEquals(array_shift($expectedDispatchCalls), $args);

        return $args[1];
      });

    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingDrawdown::getEntityName(), $drawdown->toArray())
      ->willReturn(new Result([$persistedDrawdown->toArray()]));

    static::assertEquals(
      $persistedDrawdown,
      $this->drawdownManager->createNew($payoutProcessBundle, 12.34, 56)
    );
  }

  public function testDelete(): void {
    $drawdown = DrawdownFactory::create();

    $this->api4Mock->expects(static::once())->method('deleteEntity')
      ->with(FundingDrawdown::getEntityName(), $drawdown->getId());

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(DrawdownDeletedEvent::class, new DrawdownDeletedEvent($drawdown));

    $this->drawdownManager->delete($drawdown);
  }

  public function testDeleteNewDrawdownsByPayoutProcessId(): void {
    $drawdown = DrawdownFactory::create();
    $this->api4Mock->method('getEntities')
      ->with(FundingDrawdown::getEntityName(), CompositeCondition::fromFieldValuePairs([
        'payout_process_id' => 23,
        'status' => 'new',
      ]))->willReturn(new Result([$drawdown->toArray()]));

    $this->api4Mock->expects(static::once())->method('deleteEntity')
      ->with(FundingDrawdown::getEntityName(), $drawdown->getId());

    $this->drawdownManager->deleteNewDrawdownsByPayoutProcessId(23);
  }

  public function testGet(): void {
    $drawdown = DrawdownFactory::create();

    $this->api4Mock->expects(static::once())->method('getEntity')
      ->with(FundingDrawdown::getEntityName(), $drawdown->getId())
      ->willReturn($drawdown->toArray());

    static::assertEquals($drawdown, $this->drawdownManager->get($drawdown->getId()));
  }

  public function testGetNull(): void {
    $this->api4Mock->expects(static::once())->method('getEntity')
      ->with(FundingDrawdown::getEntityName(), 12)
      ->willReturn(NULL);

    static::assertNull($this->drawdownManager->get(12));
  }

  public function testGetBy(): void {
    $drawdown = DrawdownFactory::create();
    $condition = Comparison::new('id', '=', 12);
    $this->api4Mock->method('getEntities')
      ->with(FundingDrawdown::getEntityName(), $condition)
      ->willReturn(new Result([$drawdown->toArray()]));

    static::assertEquals([$drawdown], $this->drawdownManager->getBy($condition));
  }

  public function testGetLastByPayoutProcessId(): void {
    $drawdown = DrawdownFactory::create();
    $this->api4Mock->method('getEntities')
      ->with(
        FundingDrawdown::getEntityName(),
        Comparison::new('payout_process_id', '=', 12),
        ['id' => 'DESC'],
        1
      )->willReturn(new Result([$drawdown->toArray()]));

    static::assertEquals($drawdown, $this->drawdownManager->getLastByPayoutProcessId(12));
  }

  public function testInsert(): void {
    $payoutProcessBundle = PayoutProcessBundleFactory::create();

    $drawdown = DrawdownEntity::fromArray([
      'payout_process_id' => $payoutProcessBundle->getPayoutProcess()->getId(),
      'status' => 'new',
      'creation_date' => date('Y-m-d H:i:s'),
      'amount' => 12.34,
      'acception_date' => NULL,
      'requester_contact_id' => 56,
      'reviewer_contact_id' => NULL,
    ]);
    $persistedDrawdown = DrawdownEntity::fromArray(['id' => 99] + $drawdown->toArray());

    $expectedDispatchCalls = [
      [
        DrawdownPreCreateEvent::class,
        new DrawdownPreCreateEvent(new DrawdownBundle($drawdown, $payoutProcessBundle)),
      ],
      [
        DrawdownCreatedEvent::class,
        new DrawdownCreatedEvent(new DrawdownBundle($persistedDrawdown, $payoutProcessBundle)),
      ],
    ];
    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')
      ->willReturnCallback(function (...$args) use (&$expectedDispatchCalls) {
        static::assertEquals(array_shift($expectedDispatchCalls), $args);

        return $args[1];
      });

    $this->api4Mock->expects(static::once())->method('createEntity')
      ->with(FundingDrawdown::getEntityName(), $drawdown->toArray())
      ->willReturn(new Result([$persistedDrawdown->toArray()]));

    $this->drawdownManager->insert($drawdown, $payoutProcessBundle);
    static::assertEquals($persistedDrawdown, $drawdown);
  }

}
