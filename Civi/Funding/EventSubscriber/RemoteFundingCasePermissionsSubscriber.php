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

final class RemoteFundingCasePermissionsSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [RemoteFundingDAOGetEvent::getEventName('RemoteFundingCase') => ['onGet', 100]];
  }

  public function onGet(RemoteFundingDAOGetEvent $event): void {
    $event->addDebugOutput(static::class, []);
    // permissions will be merged later
    $event
      ->addSelect('GROUP_CONCAT(DISTINCT cc.permissions) AS permissions')
      ->addJoin('FundingCaseContact AS cc', 'INNER', NULL,
        ['cc.funding_case_id', '=', 'id'])
      ->addJoin('Relationship AS relationship', 'LEFT', NULL,
        ['relationship.relationship_type_id', '=', 'cc.relationship_type_id'],
        [
          'OR',
          [
            [
              'AND',
              [
                ['relationship.contact_id_a', '=', 'cc.contact_id'],
                ['relationship.contact_id_b', '=', $event->getContactId()],
                ['cc.relationship_direction', '=', '"a_b"'],
              ],
            ],
            [
              'AND',
              [
                ['relationship.contact_id_a', '=', $event->getContactId()],
                ['relationship.contact_id_b', '=', 'cc.contact_id'],
                ['cc.relationship_direction', '=', '"b_a"'],
              ],
            ],
          ],
        ])
      ->addClause('OR', ['cc.contact_id', '=', $event->getContactId()], ['relationship.id', 'IS NOT NULL'])
      ->addGroupBy('id');
  }

}
