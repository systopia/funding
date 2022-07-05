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

namespace Civi\Funding\EventSubscriber\Remote;

use Civi\Api4\FundingCaseContactRelation;
use Civi\Funding\Event\Remote\FundingCase\PermissionsGetEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\Api4\Query\WhereParameter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

final class FundingCasePermissionsGetSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [PermissionsGetEvent::class => 'onPermissionsGet'];
  }

  /**
   * @throws \API_Exception
   */
  public function onPermissionsGet(PermissionsGetEvent $event): void {
    /** @var array<int, array{id: int, funding_case_id: int, entity_table: string, entity_id: int, parent_id: int|null, permissions: array<string>|null}> $contactRelations */
    $contactRelations = $this->api4->execute(FundingCaseContactRelation::getEntityName(), 'get', [
      'where' => WhereParameter::new(Comparison::new('funding_case_id', '=', $event->getEntityId())),
    ])->indexBy('id')->getArrayCopy();

    foreach ($contactRelations as $contactRelation) {
      if (NULL === $contactRelation['permissions']) {
        continue;
      }

      if ('civicrm_contact' === $contactRelation['entity_table']) {
        if ($event->getContactId() === $contactRelation['entity_id']) {
          $event->addPermissions($contactRelation['permissions']);
        }
      }
      elseif ('civicrm_relationship_type' === $contactRelation['entity_table']) {
        Assert::notNull($contactRelation['parent_id']);
        $parentContactRelation = $contactRelations[$contactRelation['parent_id']];
        if ('civicrm_contact' === $parentContactRelation['entity_table']
          && $this->hasRelation($event->getContactId(), $contactRelation['entity_id'],
            $parentContactRelation['entity_id'])
        ) {
          $event->addPermissions($contactRelation['permissions']);
        }
      }
    }
  }

  /**
   * @throws \API_Exception
   */
  private function hasRelation(int $contactId, int $relationshipTypeId, int $relatedContactId): bool {
    return $this->api4->execute('Relationship', 'get', [
      'select' => ['id'],
      'where' => WhereParameter::new(
        Comparison::new('relationship_type_id', '=', $relationshipTypeId),
        CompositeCondition::new('OR',
          CompositeCondition::new('AND',
            Comparison::new('contact_id_a', '=', $contactId),
            Comparison::new('contact_id_b', '=', $relatedContactId),
          ),
          CompositeCondition::new('AND',
            Comparison::new('contact_id_a', '=', $relatedContactId),
            Comparison::new('contact_id_b', '=', $contactId),
          ),
        ),
      ),
    ])->rowCount >= 1;
  }

}
