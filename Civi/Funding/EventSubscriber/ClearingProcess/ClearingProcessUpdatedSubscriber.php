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

namespace Civi\Funding\EventSubscriber\ClearingProcess;

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ClearingProcessUpdatedSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ClearingProcessUpdatedEvent::class => 'onUpdated'];
  }

  public function __construct(
    Api4Interface $api4
  ) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ClearingProcessUpdatedEvent $event): void {
    $clearingProcess = $event->getClearingProcess();
    $reportData = $clearingProcess->getReportData();

    if (!isset($reportData['grunddaten'])) {
      return;
    }
    $basicData = (array) $reportData['grunddaten'];

    if (!isset($basicData['zeitraeume'])) {
      return;
    }
    $durations = (array) $basicData['zeitraeume'];

    $indexLast = count($durations) - 1;
    if ($indexLast < 0) {
      return;
    }

    $lastDuration = (array) $durations[$indexLast];
    $startDate = $lastDuration['beginn'];
    $stopDate = $lastDuration['ende'];

    if (!isset($startDate) || !isset($stopDate)) {
      return;
    }

    $data = [
      'start_date' => $startDate,
      'end_date' => $stopDate,
    ];

    $this->api4->updateEntity(
      FundingClearingProcess::getEntityName(),
      $clearingProcess->getId(),
      $data
    );
  }

}
