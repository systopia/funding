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

use Civi\Funding\Event\RemoteFundingDAOGetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class RemoteFundingProgramPermissionsSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [RemoteFundingDAOGetEvent::getEventName('RemoteFundingProgram') => ['onGet', 100]];
  }

  public function onGet(RemoteFundingDAOGetEvent $event): void {
    $event->addDebugOutput(static::class, []);
    $event
      ->addJoin('FundingProgramContactType AS pc', 'INNER', NULL,
        ['pc.funding_program_id', '=', 'id']
      )->addJoin('ContactType AS ct', 'INNER', NULL,
        ['ct.id', '=', 'pc.contact_type_id']
      )->addJoin('Contact AS c', 'INNER', NULL,
        ['c.contact_sub_type', '=', 'ct.name']
      )->addJoin('Relationship AS relationship', 'INNER', NULL,
        ['relationship.relationship_type_id', '=', 'pc.relationship_type_id'],
        ['relationship.contact_id_b', '=', 'c.id'],
        ['relationship.contact_id_a', '=', $event->getContactId()]
      );
  }

}
