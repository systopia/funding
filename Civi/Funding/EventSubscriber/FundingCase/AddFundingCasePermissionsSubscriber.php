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

use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\FundingCase\FundingCasePermissionsInitializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds permissions to newly created FundingCase.
 */
class AddFundingCasePermissionsSubscriber implements EventSubscriberInterface {

  private FundingCasePermissionsInitializer $permissionsInitializer;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [FundingCaseCreatedEvent::class => 'onCreated'];
  }

  public function __construct(FundingCasePermissionsInitializer $permissionsInitializer) {
    $this->permissionsInitializer = $permissionsInitializer;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(FundingCaseCreatedEvent $event): void {
    $this->permissionsInitializer->initializePermissions($event->getFundingCase());
  }

}
