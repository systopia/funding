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
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

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
 *   amount_requested: float,
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
   *   amount_requested: float,
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
      'amount_requested' => $values['amount_requested'],
      'creation_date' => $now,
      'modification_date' => $now,
      'start_date' => $values['start_date'] ?? NULL,
      'end_date' => $values['end_date'] ?? NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'is_review_calculative' => NULL,
    ]);

    $event = new ApplicationProcessPreCreateEvent($contactId, $applicationProcess, $values['funding_case']);
    $this->eventDispatcher->dispatch(ApplicationProcessPreCreateEvent::class, $event);

    $action = FundingApplicationProcess::create()->setValues($applicationProcess->toArray());

    /** @phpstan-var applicationProcessT $applicationProcessValues */
    $applicationProcessValues = $this->api4->executeAction($action)->first();
    $applicationProcess = ApplicationProcessEntity::fromArray($applicationProcessValues)->reformatDates();

    $event = new ApplicationProcessCreatedEvent($contactId, $applicationProcess, $values['funding_case']);
    $this->eventDispatcher->dispatch(ApplicationProcessCreatedEvent::class, $event);

    return $applicationProcess;
  }

  public function get(int $id): ?ApplicationProcessEntity {
    $action = FundingApplicationProcess::get()->addWhere('id', '=', $id);
    /** @phpstan-var applicationProcessT|null $values */
    $values = $this->api4->executeAction($action)->first();

    if (NULL === $values) {
      return NULL;
    }

    return ApplicationProcessEntity::fromArray($values);
  }

  public function getFirstByFundingCaseId(int $fundingCaseId): ?ApplicationProcessEntity {
    $action = FundingApplicationProcess::get()
      ->addWhere('funding_case_id', '=', $fundingCaseId)
      ->addOrderBy('id')
      ->setLimit(1);

    /** @phpstan-var applicationProcessT|null $values */
    $values = $this->api4->executeAction($action)->first();

    if (NULL === $values) {
      return NULL;
    }

    return ApplicationProcessEntity::fromArray($values);
  }

  public function update(int $contactId, ApplicationProcessEntity $applicationProcess,
    FundingCaseEntity $fundingCase
  ): void {
    $applicationProcess->setModificationDate(new \DateTime(date('YmdHis')));

    $previousApplicationProcess = $this->get($applicationProcess->getId());
    Assert::notNull($previousApplicationProcess, 'Application process could not be loaded');

    $event = new ApplicationProcessPreUpdateEvent($contactId,
      $previousApplicationProcess,
      $applicationProcess,
      $fundingCase
    );
    $this->eventDispatcher->dispatch(ApplicationProcessPreUpdateEvent::class, $event);

    $action = FundingApplicationProcess::update()->setValues($applicationProcess->toArray());
    $this->api4->executeAction($action);

    $event = new ApplicationProcessUpdatedEvent($contactId,
      $previousApplicationProcess,
      $applicationProcess,
      $fundingCase
    );
    $this->eventDispatcher->dispatch(ApplicationProcessUpdatedEvent::class, $event);
  }

}
