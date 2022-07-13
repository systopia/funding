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

use Civi\Api4\FundingProgramContactRelation;
use Civi\Funding\Event\Remote\FundingProgram\PermissionsGetEvent;
use Civi\Funding\Permission\ContactRelationCheckerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingProgramPermissionsGetSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  private ContactRelationCheckerInterface $contactRelationChecker;

  public function __construct(Api4Interface $api4, ContactRelationCheckerInterface $contactRelationChecker) {
    $this->api4 = $api4;
    $this->contactRelationChecker = $contactRelationChecker;
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

      if (NULL !== $contactRelation['parent_id']) {
        $parentContactRelation = $contactRelations[$contactRelation['parent_id']];
      }
      else {
        $parentContactRelation = NULL;
      }

      if ($this->contactRelationChecker->hasRelation($event->getContactId(),
        $contactRelation, $parentContactRelation)
      ) {
        $event->addPermissions($contactRelation['permissions']);
      }
    }
  }

}
