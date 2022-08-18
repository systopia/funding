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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds permissions to newly created FundingCase.
 */
final class AddFundingCasePermissionsSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [FundingCaseCreatedEvent::class => 'onCreated'];
  }

  /**
   * @throws \API_Exception
   */
  public function onCreated(FundingCaseCreatedEvent $event): void {
    // TODO: Which relations have to be set additionally?
    $action = FundingCaseContactRelation::create()->setValues([
      'funding_case_id' => $event->getFundingCase()->getId(),
      'entity_table' => 'civicrm_contact',
      'entity_id' => $event->getContactId(),
      'permissions' => $this->getCreatingContactPermissions($event),
    ]);
    $this->api4->executeAction($action);
  }

  /**
   * @param \Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent $event
   *
   * @phpstan-return array<string>
   */
  public function getCreatingContactPermissions(FundingCaseCreatedEvent $event): array {
    // TODO: Initial permissions of the creating contact?
    return array_merge(['modify_application'], array_filter($event->getFundingProgram()->getPermissions(),
      fn (string $permission) => 'create_application' !== $permission));
  }

}
