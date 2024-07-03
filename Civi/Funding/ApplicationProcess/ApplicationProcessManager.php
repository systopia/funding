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
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessDeletedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreDeleteEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Form\Application\ValidatedApplicationDataInterface;
use Civi\Funding\Util\DateTimeUtil;
use Civi\Funding\Util\Uuid;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Webmozart\Assert\Assert;

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
  public function countBy(ConditionInterface $where): int {
    return $this->api4->countEntities(
      FundingApplicationProcess::getEntityName(),
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
    // @phpstan-ignore-next-line Because of possible extra values through mapped data.
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
      'is_review_content' => NULL,
      'reviewer_cont_contact_id' => NULL,
      'is_review_calculative' => NULL,
      'reviewer_calc_contact_id' => NULL,
      'is_eligible' => NULL,
    ] + $data->getMappedData());

    $event = new ApplicationProcessPreCreateEvent(
      $contactId,
      new ApplicationProcessEntityBundle($applicationProcess, $fundingCase, $fundingCaseType, $fundingProgram)
    );
    $this->eventDispatcher->dispatch(ApplicationProcessPreCreateEvent::class, $event);

    $action = FundingApplicationProcess::create(FALSE)
      ->setValues($applicationProcess->toArray());

    $applicationProcess = ApplicationProcessEntity::singleFromApiResult($this->api4->executeAction($action))
      ->reformatDates();
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

    return ApplicationProcessEntity::singleOrNullFromApiResult($this->api4->executeAction($action));
  }

  /**
   * @phpstan-return array<string, mixed>
   *
   * @throws \CRM_Core_Exception
   */
  public function getCustomFields(ApplicationProcessEntity $applicationProcess): array {
    $customFields = $this->api4->execute(FundingApplicationProcess::getEntityName(), 'get', [
      'select' => ['custom.*'],
      'where' => [['id', '=', $applicationProcess->getId()]],
    ])->single();
    unset($customFields['id']);

    return $customFields;
  }

  /**
   * @phpstan-return list<ApplicationProcessEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getAll(): array {
    // @phpstan-ignore-next-line
    return ApplicationProcessEntity::allFromApiResult(
      $this->api4->getEntities(FundingApplicationProcess::getEntityName())
    );
  }

  /**
   * @phpstan-param array<string, 'ASC'|'DESC'> $orderBy
   *
   * @phpstan-return array<ApplicationProcessEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getBy(
    ConditionInterface $condition,
    array $orderBy = [],
    int $limit = 0,
    int $offset = 0,
    ?string $indexBy = NULL
  ): array {
    $result = $this->api4->getEntities(
      FundingApplicationProcess::getEntityName(),
      $condition,
      $orderBy,
      $limit,
      $offset
    );

    if (NULL !== $indexBy) {
      $result->indexBy($indexBy);
    }

    return ApplicationProcessEntity::allFromApiResult($result);
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * @phpstan-return array<int, FullApplicationProcessStatus>
   *   Status of other application processes in same funding case indexed by ID.
   */
  public function getStatusListByFundingCaseId(int $fundingCaseId): array {
    $action = $this->api4->createGetAction(FundingApplicationProcess::getEntityName())
      ->setCheckPermissions(FALSE)
      ->addSelect('id', 'status', 'is_review_calculative', 'is_review_content')
      ->addWhere('funding_case_id', '=', $fundingCaseId);

    $statusList = [];
    $result = $this->api4->executeAction($action);
    /** @phpstan-var array{id: int, status: string, is_review_calculative: bool|null, is_review_content: bool|null} $values */
    foreach ($result as $values) {
      $statusList[$values['id']] = new FullApplicationProcessStatus(
        $values['status'], $values['is_review_calculative'], $values['is_review_content']
      );
    }

    return $statusList;
  }

  /**
   * @phpstan-return array<int, ApplicationProcessEntity>
   *   Indexed by id.
   *
   * @throws \CRM_Core_Exception
   */
  public function getByFundingCaseId(int $fundingCaseId): array {
    return $this->getBy(Comparison::new('funding_case_id', '=', $fundingCaseId), [], 0, 0, 'id');
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getFirstByFundingCaseId(int $fundingCaseId): ?ApplicationProcessEntity {
    return $this->getBy(Comparison::new('funding_case_id', '=', $fundingCaseId), ['id' => 'ASC'], 1)[0] ?? NULL;
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

    $action = (new DAODeleteAction(FundingApplicationProcess::getEntityName(), 'delete'))
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $applicationProcessBundle->getApplicationProcess()->getId());
    $this->api4->executeAction($action);

    $deletedEvent = new ApplicationProcessDeletedEvent($applicationProcessBundle);
    $this->eventDispatcher->dispatch(ApplicationProcessDeletedEvent::class, $deletedEvent);
  }

}
