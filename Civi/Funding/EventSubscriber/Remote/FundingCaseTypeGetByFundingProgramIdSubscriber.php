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

use Civi\Api4\FundingCaseType;
use Civi\Funding\Event\Remote\FundingCaseType\GetByFundingProgramIdEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class FundingCaseTypeGetByFundingProgramIdSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      GetByFundingProgramIdEvent::getEventName() => 'onGetByFundingProgramId',
    ];
  }

  /**
   * @throws \API_Exception
   */
  public function onGetByFundingProgramId(GetByFundingProgramIdEvent $event): void {
    $action = FundingCaseType::getByFundingProgramId($event->isCheckPermissions())
      ->setDebug($event->isDebug())
      ->setFundingProgramId($event->getFundingProgramId());

    $result = $this->api4->executeAction($action);
    /** @var array<array<string, mixed>> $records */
    $records = $result->getArrayCopy();

    $event
      ->setRecords($records)
      ->addDebugOutput(static::class, $result->debug ?? []);
  }

}
