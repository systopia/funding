<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\Funding\Permission\ContactRelation\Types\ContactRelationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingCase\FundingCaseContactRelationUpdateSubscriber
 */
final class FundingCaseContactRelationSubscriberTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\EventSubscriber\FundingCase\FundingCaseContactRelationUpdateSubscriber
   */
  private FundingCaseContactRelationUpdateSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->subscriber = new FundingCaseContactRelationUpdateSubscriber($this->api4Mock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      FundingCaseUpdatedEvent::class => 'onFundingCaseUpdated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnFundingCaseUpdated(): void {
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase(['recipient_contact_id' => 123]);
    $previousFundingCase = clone $fundingCase;

    // Changing recipient contact should clear cache.
    $fundingCase->setRecipientContactId(1234);

    $this->api4Mock->expects(static::once())->method('getEntities')
      ->with(FundingCaseContactRelation::getEntityName(), CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $fundingCase->getId(),
        'type' => ContactRelationship::NAME,
      ]))
      ->willReturn(new Result([
        [
          'id' => 2,
          'funding_case_id' => $fundingCase->getId(),
          'type' => ContactRelationship::NAME,
          'properties' => [
            'contactId' => 12,
            'relationshipTypeId' => 9,
          ],
        ],
        [
          'id' => 3,
          'funding_case_id' => $fundingCase->getId(),
          'type' => ContactRelationship::NAME,
          'properties' => [
            'contactId' => 123,
            'relationshipTypeId' => 9,
          ],
        ],
      ]));

    $this->api4Mock->expects(static::once())->method('updateEntity')
      ->with(FundingCaseContactRelation::getEntityName(), 3, [
        'id' => 3,
        'funding_case_id' => $fundingCase->getId(),
        'type' => ContactRelationship::NAME,
        'properties' => [
          'contactId' => 1234,
          'relationshipTypeId' => 9,
        ],
      ]);

    $this->subscriber->onFundingCaseUpdated(
      new FundingCaseUpdatedEvent($previousFundingCase, $fundingCase, $fundingCaseType)
    );
  }

}
