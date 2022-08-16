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

namespace Civi\Funding\ApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @phpstan-type applicationProcessT array{
 *   id: int,
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   title: string,
 *   short_description: string,
 *   start_date: string|null,
 *   end_date: string|null,
 *   request_data: array<string, mixed>,
 *   amount_granted: float|null,
 *   granted_budget: float|null,
 *   is_review_content: bool|null,
 *   is_review_calculative: bool|null,
 * }
 */
class ApplicationProcessManager {

  private Api4Interface $api4;

  private CiviEventDispatcher $eventDispatcher;

  public function __construct(Api4Interface $api4, CiviEventDispatcher $eventDispatcher) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @phpstan-param array{
   *   funding_case: \Civi\Funding\Entity\FundingCaseEntity,
   *   status: string,
   *   title: string,
   *   short_description: string,
   *   request_data: array<string, mixed>,
   *   start_date?: ?string,
   *   end_date?: ?string,
   * } $values
   */
  public function create(int $contactId, array $values): ApplicationProcessEntity {
    /** @var string $now */
    $now = date('Y-m-d H:i:s');
    $applicationProcess = ApplicationProcessEntity::fromArray([
      'funding_case_id' => $values['funding_case']->getId(),
      'status' => $values['status'],
      'title' => $values['title'],
      'short_description' => $values['short_description'],
      'request_data' => $values['request_data'],
      'creation_date' => $now,
      'modification_date' => $now,
      'start_date' => $values['start_date'] ?? NULL,
      'end_date' => $values['end_date'] ?? NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'is_review_calculative' => NULL,
    ]);
    $action = FundingApplicationProcess::create()->setValues($applicationProcess->toArray());

    /** @phpstan-var applicationProcessT $applicationProcessValues */
    $applicationProcessValues = $this->api4->executeAction($action)->first();
    $applicationProcess = ApplicationProcessEntity::fromArray($applicationProcessValues);

    $event = new ApplicationProcessCreatedEvent($contactId, $applicationProcess, $values['funding_case']);
    $this->eventDispatcher->dispatch(ApplicationProcessCreatedEvent::class, $event);

    return $applicationProcess;
  }

  public function update(int $contactId, ApplicationProcessEntity $applicationProcess,
    FundingCaseEntity $fundingCase
  ): void {
    $applicationProcess->setModificationDate(new \DateTime(date('YmdHis')));

    $action = FundingApplicationProcess::update()->setValues($applicationProcess->toArray());
    $this->api4->executeAction($action);

    $event = new ApplicationProcessUpdatedEvent($contactId, $applicationProcess, $fundingCase);
    $this->eventDispatcher->dispatch(ApplicationProcessUpdatedEvent::class, $event);
  }

}
