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

namespace Civi\Funding\ClearingProcess;

use Civi\Api4\FundingClearingProcess;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Event\ClearingProcess\ClearingProcessCreatedEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessPreCreateEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessPreUpdateEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessStartedEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Webmozart\Assert\Assert;

class ClearingProcessManager {

  private Api4Interface $api4;

  private CiviEventDispatcherInterface $eventDispatcher;

  public function __construct(Api4Interface $api4, CiviEventDispatcherInterface $eventDispatcher) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function create(ApplicationProcessEntityBundle $applicationProcessBundle): ClearingProcessEntity {
    $clearingProcess = ClearingProcessEntity::fromArray([
      'application_process_id' => $applicationProcessBundle->getApplicationProcess()->getId(),
      'status' => 'not-started',
      'creation_date' => NULL,
      'modification_date' => NULL,
      'start_date' => NULL,
      'end_date' => NULL,
      'report_data' => [],
      'is_review_content' => NULL,
      'reviewer_cont_contact_id' => NULL,
      'is_review_calculative' => NULL,
      'reviewer_calc_contact_id' => NULL,
    ]);

    $event = new ClearingProcessPreCreateEvent(
      new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle)
    );
    $this->eventDispatcher->dispatch(ClearingProcessPreCreateEvent::class, $event);

    $result = $this->api4->createEntity(FundingClearingProcess::getEntityName(), $clearingProcess->toArray());
    $clearingProcess = ClearingProcessEntity::singleFromApiResult($result)
      ->reformatDates();

    $event = new ClearingProcessCreatedEvent(
      new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle)
    );
    $this->eventDispatcher->dispatch(ClearingProcessCreatedEvent::class, $event);

    return $clearingProcess;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $id): ?ClearingProcessEntity {
    $values = $this->api4->getEntity(FundingClearingProcess::getEntityName(), $id);

    // @phpstan-ignore-next-line
    return NULL === $values ? NULL : ClearingProcessEntity::fromArray($values);
  }

  public function getByApplicationProcessId(int $applicationProcessId): ?ClearingProcessEntity {
    return ClearingProcessEntity::singleOrNullFromApiResult(
      $this->api4->getEntities(
        FundingClearingProcess::getEntityName(),
        Comparison::new('application_process_id', '=', $applicationProcessId)
      )
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function start(ClearingProcessEntityBundle $clearingProcessBundle): void {
    $clearingProcess = $clearingProcessBundle->getClearingProcess();
    $clearingProcess->setStatus('draft');
    $clearingProcess->setCreationDate(new \DateTime(date('YmdHis')));

    $this->update($clearingProcessBundle);

    $event = new ClearingProcessStartedEvent($clearingProcessBundle);
    $this->eventDispatcher->dispatch(ClearingProcessStartedEvent::class, $event);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function update(ClearingProcessEntityBundle $clearingProcessBundle): void {
    $clearingProcess = $clearingProcessBundle->getClearingProcess();
    $clearingProcess->setModificationDate(new \DateTime(date('YmdHis')));
    $previousClearingProcess = $this->get($clearingProcess->getId());
    Assert::notNull($previousClearingProcess);

    $event = new ClearingProcessPreUpdateEvent($previousClearingProcess, $clearingProcessBundle);
    $this->eventDispatcher->dispatch(ClearingProcessPreUpdateEvent::class, $event);

    $this->api4->updateEntity(
      FundingClearingProcess::getEntityName(),
      $clearingProcess->getId(),
      $clearingProcess->toArray()
    );

    $event = new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcessBundle);
    $this->eventDispatcher->dispatch(ClearingProcessUpdatedEvent::class, $event);
  }

}
