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
use Civi\Api4\Generic\DAODeleteAction;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessDeletedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreDeleteEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Form\ValidatedApplicationDataInterface;
use Civi\Funding\Util\DateTimeUtil;
use Civi\Funding\Util\Uuid;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type applicationProcessT array{
 *   id: int,
 *   identifier: string,
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
 *   reviewer_cont_contact_id: int|null,
 *   is_review_calculative: bool|null,
 *   reviewer_calc_contact_id: int|null,
 * }
 */
class ApplicationProcessManager {

  private Api4Interface $api4;

  private CiviEventDispatcherInterface $eventDispatcher;

  public function __construct(Api4Interface $api4, CiviEventDispatcherInterface $eventDispatcher) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function countBy(CompositeCondition $where): int {
    return $this->api4->countEntities(
      FundingApplicationProcess::_getEntityName(),
      $where,
      ['checkPermissions' => FALSE],
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function countByFundingCaseId(int $fundingCaseId): int {
    $action = FundingApplicationProcess::get(FALSE)
      ->addWhere('funding_case_id', '=', $fundingCaseId)
      ->selectRowCount();

    return $this->api4->executeAction($action)->countMatched();
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function create(
    int $contactId,
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram,
    string $status,
    ValidatedApplicationDataInterface $data
  ): ApplicationProcessEntity {
    /** @var string $now */
    $now = date('Y-m-d H:i:s');
    $applicationProcess = ApplicationProcessEntity::fromArray([
      // Initialize with random UUID
      'identifier' => Uuid::generateRandom(),
      'funding_case_id' => $fundingCase->getId(),
      'status' => $status,
      'title' => $data->getTitle(),
      'short_description' => $data->getShortDescription(),
      'request_data' => $data->getApplicationData(),
      'amount_requested' => $data->getAmountRequested(),
      'creation_date' => $now,
      'modification_date' => $now,
      'start_date' => DateTimeUtil::toDateTimeStrOrNull($data->getStartDate()),
      'end_date' => DateTimeUtil::toDateTimeStrOrNull($data->getEndDate()),
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'reviewer_cont_contact_id' => NULL,
      'is_review_calculative' => NULL,
      'reviewer_calc_contact_id' => NULL,
    ]);

    $event = new ApplicationProcessPreCreateEvent(
      $contactId,
      new ApplicationProcessEntityBundle($applicationProcess, $fundingCase, $fundingCaseType, $fundingProgram)
    );
    $this->eventDispatcher->dispatch(ApplicationProcessPreCreateEvent::class, $event);

    $action = FundingApplicationProcess::create(FALSE)
      ->setValues($applicationProcess->toArray());

    /** @phpstan-var applicationProcessT $applicationProcessValues */
    $applicationProcessValues = $this->api4->executeAction($action)->first();
    $applicationProcess = ApplicationProcessEntity::fromArray($applicationProcessValues)->reformatDates();
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram
    );

    $event = new ApplicationProcessCreatedEvent($contactId, $applicationProcessBundle);
    $this->eventDispatcher->dispatch(ApplicationProcessCreatedEvent::class, $event);

    return $applicationProcess;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $id): ?ApplicationProcessEntity {
    $action = FundingApplicationProcess::get(FALSE)
      ->addWhere('id', '=', $id);
    /** @phpstan-var applicationProcessT|null $values */
    $values = $this->api4->executeAction($action)->first();

    if (NULL === $values) {
      return NULL;
    }

    return ApplicationProcessEntity::fromArray($values);
  }

  /**
   * @phpstan-return array<ApplicationProcessEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getByFundingCaseId(int $fundingCaseId): array {
    return ApplicationProcessEntity::allFromApiResult(
      $this->api4->getEntities(
        FundingApplicationProcess::_getEntityName(),
        Comparison::new('funding_case_id', '=', $fundingCaseId),
        [],
        0,
        0,
        ['checkPermissions' => FALSE],
      )
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFirstByFundingCaseId(int $fundingCaseId): ?ApplicationProcessEntity {
    $action = FundingApplicationProcess::get(FALSE)
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

  /**
   * @throws \CRM_Core_Exception
   */
  public function update(int $contactId, ApplicationProcessEntityBundle $applicationProcessBundle): void {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $applicationProcess->setModificationDate(new \DateTime(date('YmdHis')));

    $previousApplicationProcess = $this->get($applicationProcess->getId());
    Assert::notNull($previousApplicationProcess, 'Application process could not be loaded');

    $event = new ApplicationProcessPreUpdateEvent(
      $contactId,
      $previousApplicationProcess,
      $applicationProcessBundle,
    );
    $this->eventDispatcher->dispatch(ApplicationProcessPreUpdateEvent::class, $event);

    $action = FundingApplicationProcess::update(FALSE)
      ->setValues($applicationProcess->toArray());
    $this->api4->executeAction($action);

    $event = new ApplicationProcessUpdatedEvent(
      $contactId,
      $previousApplicationProcess,
      $applicationProcessBundle,
    );
    $this->eventDispatcher->dispatch(ApplicationProcessUpdatedEvent::class, $event);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function delete(ApplicationProcessEntityBundle $applicationProcessBundle): void {
    $preDeleteEvent = new ApplicationProcessPreDeleteEvent($applicationProcessBundle);
    $this->eventDispatcher->dispatch(ApplicationProcessPreDeleteEvent::class, $preDeleteEvent);

    $action = (new DAODeleteAction(FundingApplicationProcess::_getEntityName(), 'delete'))
      ->addWhere('id', '=', $applicationProcessBundle->getApplicationProcess()->getId());
    $this->api4->executeAction($action);

    $deletedEvent = new ApplicationProcessDeletedEvent($applicationProcessBundle);
    $this->eventDispatcher->dispatch(ApplicationProcessDeletedEvent::class, $deletedEvent);
  }

}
