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
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\Funding\Permission\ContactRelation\Types\ContactRelationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * When the recipient contact of a funding case is changed this subscriber
 * changes the contact ID of FundingCaseContactRelation records with type
 * ContactRelationship to the new recipient contact ID if it is equal to the
 * previous recipient contact ID.
 */
final class FundingCaseContactRelationUpdateSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      FundingCaseUpdatedEvent::class => 'onFundingCaseUpdated',
    ];
  }

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onFundingCaseUpdated(FundingCaseUpdatedEvent $event): void {
    if ($event->getFundingCase()->getRecipientContactId()
      === $event->getPreviousFundingCase()->getRecipientContactId()
    ) {
      return;
    }

    $previousRecipientContactId = $event->getPreviousFundingCase()->getRecipientContactId();
    $newRecipientContactId = $event->getFundingCase()->getRecipientContactId();

    /** @phpstan-var list<array{id: int, properties: array{relationshipTypeId: int, contactId: int}}> $relations */
    $relations = $this->api4->getEntities(
      FundingCaseContactRelation::getEntityName(),
      CompositeCondition::fromFieldValuePairs([
        'funding_case_id' => $event->getFundingCase()->getId(),
        'type' => ContactRelationship::NAME,
      ])
    );

    foreach ($relations as $relation) {
      if ($relation['properties']['contactId'] === $previousRecipientContactId) {
        $relation['properties']['contactId'] = $newRecipientContactId;
        $this->api4->updateEntity(FundingCaseContactRelation::getEntityName(), $relation['id'], $relation);
      }
    }
  }

}
