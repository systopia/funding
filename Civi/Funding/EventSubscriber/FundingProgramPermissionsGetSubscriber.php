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

namespace Civi\Funding\EventSubscriber;

use Civi\Api4\ContactType;
use Civi\Api4\FundingProgramContactRelation;
use Civi\Api4\Relationship;
use Civi\Funding\Event\Remote\FundingProgram\PermissionsGetEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

final class FundingProgramPermissionsGetSubscriber implements EventSubscriberInterface {

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
    $action = FundingProgramContactRelation::get()
      ->addWhere('funding_program_id', '=', $event->getEntityId());

    /** @var array<int, array{id: int, funding_program_id: int, entity_table: string, entity_id: int, parent_id: int|null, permissions: array<string>|null}> $contactRelations */
    $contactRelations = $this->api4->executeAction($action)->indexBy('id')->getArrayCopy();

    foreach ($contactRelations as $contactRelation) {
      // A relation that is used as parent might not have permissions
      if (NULL === $contactRelation['permissions']) {
        continue;
      }

      if ('civicrm_contact_type' === $contactRelation['entity_table']) {
        if ($this->hasContactType($event->getContactId(), $contactRelation['entity_id'])) {
          $event->addPermissions($contactRelation['permissions']);
        }
      }
      elseif ('civicrm_relationship_type' === $contactRelation['entity_table']) {
        Assert::notNull($contactRelation['parent_id']);
        $parentContactRelation = $contactRelations[$contactRelation['parent_id']];
        if ('civicrm_contact_type' === $parentContactRelation['entity_table']
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
  private function hasContactType(int $contactId, int $contactTypeId): bool {
    $action = ContactType::get()
      ->addSelect('id')
      ->addWhere('id', '=', $contactTypeId)
      ->addJoin('Contact AS c', 'INNER', NULL,
        CompositeCondition::new('AND',
          Comparison::new('c.id', '=', $contactId),
          CompositeCondition::new('OR',
            Comparison::new('c.contact_type', '=', 'name'),
            Comparison::new('c.contact_sub_type', '=', 'name'),
          ),
        )->toArray(),
      );

    return $this->api4->executeAction($action)->rowCount === 1;
  }

  /**
   * @throws \API_Exception
   */
  private function hasRelation(int $contactId, int $relationshipTypeId, int $contactTypeId): bool {
    $action = Relationship::get()
      ->addSelect('id')
      ->addWhere('relationship_type_id', '=', $relationshipTypeId)
      ->addClause('OR',
        ['contact_id_a', '=', $contactId],
        ['contact_id_b', '=', $contactId],
      )
      ->addJoin('ContactType AS ct', 'INNER', NULL,
        ['ct.id', '=', $contactTypeId]
      )
      ->addJoin('Contact AS c', 'INNER', NULL,
        CompositeCondition::new('AND',
          CompositeCondition::new('OR',
            Comparison::new('c.contact_type', '=', 'ct.name'),
            Comparison::new('c.contact_sub_type', '=', 'ct.name'),
          ),
          CompositeCondition::new('OR',
            CompositeCondition::new('AND',
              Comparison::new('c.id', '=', 'contact_id_a'),
              Comparison::new('contact_id_a', '!=', $contactId),
            ),
            CompositeCondition::new('AND',
              Comparison::new('c.id', '=', 'contact_id_b'),
              Comparison::new('contact_id_b', '!=', $contactId),
            ),
          ),
        )->toArray()
      );

    return $this->api4->executeAction($action)->rowCount >= 1;
  }

}
