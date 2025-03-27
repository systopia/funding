<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\EventSubscriber;

use Civi\Api4\FundingCase;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\HiHConstants;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class HiHApproveSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];
  }

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    if ($event->getFundingCaseType()->getName() !== HiHConstants::FUNDING_CASE_TYPE_NAME) {
      return;
    }

    $applicationProcess = $event->getApplicationProcess();
    if ($this->isStatusChangedToApproved($applicationProcess, $event->getPreviousApplicationProcess())) {
      if (abs($applicationProcess->getAmountRequested() - $this->getAmountApproved($applicationProcess))
        > PHP_FLOAT_EPSILON
      ) {
        $applicationProcess->setStatus('approved_partial');
      }
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    if ($event->getFundingCaseType()->getName() !== HiHConstants::FUNDING_CASE_TYPE_NAME) {
      return;
    }

    if ($event->getFundingCase()->getStatus() === 'open'
      && $this->isStatusChangedToApproved($event->getApplicationProcess(), $event->getPreviousApplicationProcess())
    ) {
      $this->api4->execute(FundingCase::getEntityName(), 'approve', [
        'id' => $event->getFundingCase()->getId(),
        'amount' => $this->getAmountApproved($event->getApplicationProcess()),
      ]);
    }
  }

  private function isStatusChangedToApproved(
    ApplicationProcessEntity $applicationProcess,
    ApplicationProcessEntity $previousApplicationProcess
  ): bool {
    return $applicationProcess->getStatus() !== $previousApplicationProcess->getStatus()
      && in_array($applicationProcess->getStatus(), ['approved', 'approved_partial'], TRUE);
  }

  private function getAmountApproved(ApplicationProcessEntity $applicationProcess): float {
    /** @phpstan-var array<string, mixed> $kosten */
    $kosten = $applicationProcess->getRequestData()['kosten'];

    return round($kosten['personalkostenBewilligt'] + $kosten['honorareBewilligt'] + $kosten['sachkostenBewilligt'], 2);
  }

}
