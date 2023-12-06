<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\ClearingProcess\ClearingProcessCreatedEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessPreCreateEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessPreUpdateEvent;
use Civi\Funding\Event\ClearingProcess\ClearingProcessUpdatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Webmozart\Assert\Assert;

final class ClearingProcessManager {

  private Api4Interface $api4;

  private CiviEventDispatcherInterface $eventDispatcher;

  public function __construct(Api4Interface $api4, CiviEventDispatcherInterface $eventDispatcher) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function create(FundingCaseEntity $fundingCase): ClearingProcessEntity {
    /** @var string $now */
    $now = date('Y-m-d H:i:s');
    $clearingProcess = ClearingProcessEntity::fromArray([
      'funding_case_id' => $fundingCase->getId(),
      'status' => 'draft',
      'creation_date' => $now,
      'modification_date' => $now,
      'report_data' => [],
    ]);

    $event = new ClearingProcessPreCreateEvent($clearingProcess, $fundingCase);
    $this->eventDispatcher->dispatch(ClearingProcessPreCreateEvent::class, $event);

    $result = $this->api4->createEntity(FundingClearingProcess::getEntityName(), $clearingProcess->toArray());
    $clearingProcess = ClearingProcessEntity::singleFromApiResult($result)
      ->reformatDates();

    $event = new ClearingProcessCreatedEvent($clearingProcess, $fundingCase);
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

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFirstByFundingCaseId(int $fundingCaseId): ?ClearingProcessEntity {
    $result = $this->api4->getEntities(
      FundingClearingProcess::getEntityName(),
      Comparison::new('funding_case_id', '=', $fundingCaseId),
      ['id' => 'ASC'],
      1
    );

    return ClearingProcessEntity::singleOrNullFromApiResult($result);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function update(ClearingProcessEntity $clearingProcess): void {
    $clearingProcess->setModificationDate(new \DateTime(date('YmdHis')));
    $previousClearingProcess = $this->get($clearingProcess->getId());
    Assert::notNull($previousClearingProcess);

    $event = new ClearingProcessPreUpdateEvent($previousClearingProcess, $clearingProcess);
    $this->eventDispatcher->dispatch(ClearingProcessPreUpdateEvent::class, $event);

    $this->api4->updateEntity(
      FundingClearingProcess::getEntityName(),
      $clearingProcess->getId(),
      $clearingProcess->toArray()
    );

    $event = new ClearingProcessUpdatedEvent($previousClearingProcess, $clearingProcess);
    $this->eventDispatcher->dispatch(ClearingProcessUpdatedEvent::class, $event);
  }

}
