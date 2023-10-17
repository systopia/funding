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

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Api4\FundingCase;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\FundingCase\FundingCaseIdentifierGeneratorInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingCase\FundingCaseIdentifierSubscriber
 */
final class FundingCaseIdentifierSubscriberTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseIdentifierGeneratorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseIdentifierGeneratorMock;

  /**
   * @var \Civi\Funding\EventSubscriber\FundingCase\FundingCaseIdentifierSubscriber
   */
  private FundingCaseIdentifierSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->fundingCaseIdentifierGeneratorMock = $this->createMock(FundingCaseIdentifierGeneratorInterface::class);
    $this->subscriber = new FundingCaseIdentifierSubscriber(
      $this->api4Mock,
      $this->fundingCaseIdentifierGeneratorMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      FundingCaseCreatedEvent::class => ['onCreated', PHP_INT_MAX],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnCreated(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $event = new FundingCaseCreatedEvent(1, $fundingCase, $fundingProgram, $fundingCaseType);

    $this->fundingCaseIdentifierGeneratorMock->method('generateIdentifier')
      ->with($fundingCase, $fundingCaseType, $fundingProgram)
      ->willReturn('generated');

    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(
        FundingCase::getEntityName(),
        $fundingCase->getId(),
        ['identifier' => 'generated'],
        ['checkPermissions' => FALSE],
      );

    $this->subscriber->onCreated($event);
    static::assertSame('generated', $fundingCase->getIdentifier());
  }

}
