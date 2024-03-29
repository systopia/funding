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

namespace Civi\Funding\EventSubscriber\FundingProgram;

use Civi\Api4\FundingProgramContactRelation;
use Civi\Funding\Event\FundingProgram\GetPermissionsEvent;
use Civi\Funding\Permission\ContactRelation\ContactRelationCheckerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @phpstan-type contactRelationT array{
 *   id: int,
 *   type: string,
 *   properties: array<string, mixed>,
 *   permissions: list<string>,
 * }
 */
class FundingProgramPermissionsGetSubscriber implements EventSubscriberInterface {

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
    return [GetPermissionsEvent::class => 'onPermissionsGet'];
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPermissionsGet(GetPermissionsEvent $event): void {
    $action = FundingProgramContactRelation::get(FALSE)
      ->addWhere('funding_program_id', '=', $event->getEntityId());

    /** @phpstan-var contactRelationT $contactRelation */
    foreach ($this->api4->executeAction($action) as $contactRelation) {
      if ($this->contactRelationChecker->hasRelation(
        $event->getContactId(),
        $contactRelation['type'],
        $contactRelation['properties']
      )) {
        $event->addPermissions($contactRelation['permissions']);
      }
    }
  }

}
