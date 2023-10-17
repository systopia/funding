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
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\Event\PayoutProcess\DrawdownAcceptedEvent;
use Civi\Funding\Event\PayoutProcess\DrawdownDeletedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
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

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(12345);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);

    $this->drawdownManager = new DrawdownManager(
      $this->api4Mock,
      $this->eventDispatcherMock,
    );
  }

  public function testAccept(): void {
    $drawdown = DrawdownFactory::create();

    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(
        FundingDrawdown::getEntityName(),
        $drawdown->getId(),
        [
          'status' => 'accepted',
          'reviewer_contact_id' => 2,
          'acception_date' => date('Y-m-d H:i:s'),
        ] + $drawdown->toArray(),
        ['checkPermissions' => FALSE],
      );

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(DrawdownAcceptedEvent::class, new DrawdownAcceptedEvent($drawdown));

    $this->drawdownManager->accept($drawdown, 2);
    static::assertSame('accepted', $drawdown->getStatus());
    static::assertSame(2, $drawdown->getReviewerContactId());
    static::assertEquals(new \DateTime('@12345'), $drawdown->getAcceptionDate());
  }

  public function testDelete(): void {
    $drawdown = DrawdownFactory::create();

    $this->api4Mock->expects(static::once())->method('execute')
      ->with(
        FundingDrawdown::getEntityName(),
        'delete',
        [
          'where' => [['id', '=', $drawdown->getId()]],
          'checkPermissions' => FALSE,
        ]
      );

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(DrawdownDeletedEvent::class, new DrawdownDeletedEvent($drawdown));

    $this->drawdownManager->delete($drawdown);
  }

  public function testGet(): void {
    $drawdown = DrawdownFactory::create();

    $this->api4Mock->expects(static::once())->method('getEntities')
      ->with(
        FundingDrawdown::getEntityName(),
        Comparison::new('id', '=', $drawdown->getId()),
        [],
        1,
        0,
        ['checkPermissions' => FALSE],
      )->willReturn(new Result([$drawdown->toArray()]));

    static::assertEquals($drawdown, $this->drawdownManager->get($drawdown->getId()));
  }

  public function testGetNull(): void {
    $this->api4Mock->expects(static::once())->method('getEntities')
      ->with(
        FundingDrawdown::getEntityName(),
        Comparison::new('id', '=', 12),
        [],
        1,
        0,
        ['checkPermissions' => FALSE],
      )->willReturn(new Result());

    static::assertNull($this->drawdownManager->get(12));
  }

}
