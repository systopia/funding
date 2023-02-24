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

use Civi\Api4\Activity;
use Civi\Api4\EntityActivity;
use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingApplicationProcessActivity;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Webmozart\Assert\Assert;

class ApplicationProcessActivityManager {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \API_Exception
   */
  public function addActivity(
    int $contactId,
    ApplicationProcessEntity $applicationProcess,
    ActivityEntity $activity
  ): void {
    $values = $activity->toArray() + [
      'source_contact_id' => $contactId,
      'source_record_id' => $applicationProcess->getFundingCaseId(),
    ];
    if (!array_key_exists('status_id', $values)) {
      $values['status_id:name'] ??= 'Completed';
    }
    $createAction = Activity::create(FALSE)
      ->setValues($values);

    /** @phpstan-var array{id: int}&array<string, mixed> $activityValues */
    $activityValues = $this->api4->executeAction($createAction)->single();
    $connectAction = EntityActivity::connect(FALSE)
      ->setActivityId($activityValues['id'])
      ->setEntity(FundingApplicationProcess::_getEntityName())
      ->setEntityId($applicationProcess->getId());

    $this->api4->executeAction($connectAction);

    $getAction = Activity::get(FALSE)
      ->addSelect('*', 'custom.*')
      ->addWhere('id', '=', $activityValues['id']);

    /** @phpstan-ignore-next-line */
    $activity->setValues($this->api4->executeAction($getAction)->single());
  }

  /**
   * @throws \API_Exception
   */
  public function deleteByApplicationProcess(int $applicationProcessId): void {
    foreach ($this->getByApplicationProcess($applicationProcessId) as $activity) {
      $action = Activity::delete(FALSE)
        ->addWhere('id', '=', $activity->getId());
      $this->api4->executeAction($action);
    }
  }

  /**
   * @phpstan-return array<ActivityEntity>
   *
   * @throws \API_Exception
   */
  public function getByApplicationProcess(int $applicationProcessId, ?ConditionInterface $condition = NULL): array {
    $action = FundingApplicationProcessActivity::get(FALSE)
      ->setApplicationProcessId($applicationProcessId);

    if (NULL !== $condition) {
      $action->setWhere([$condition->toArray()]);
    }

    return ActivityEntity::allFromApiResult($this->api4->executeAction($action));
  }

  /**
   * @phpstan-return array<ActivityEntity>
   *
   * @throws \API_Exception
   */
  public function getOpenByApplicationProcess(int $applicationProcessId, ?ConditionInterface $condition = NULL): array {
    $action = FundingApplicationProcessActivity::get(FALSE)
      ->setApplicationProcessId($applicationProcessId);

    if (NULL !== $condition) {
      $action->setWhere([$condition->toArray()]);
    }
    $action->addWhere('status_id:name', 'IN', ['Scheduled', 'Available']);

    return ActivityEntity::allFromApiResult($this->api4->executeAction($action));
  }

  /**
   * @param int|null $contactId NULL to unassign.
   *
   * @throws \API_Exception
   */
  public function assignActivity(ActivityEntity $activity, ?int $contactId): void {
    Assert::false($activity->isNew(), 'Activity is not persisted');

    $this->api4->updateEntity(
      'Activity',
      $activity->getId(),
      ['assignee_contact_id' => $contactId],
      ['checkPermissions' => FALSE],
    );
  }

  /**
   * @throws \API_Exception
   */
  public function cancelActivity(ActivityEntity $activity): void {
    $this->changeActivityStatus($activity, 'Cancelled');
  }

  /**
   * @throws \API_Exception
   */
  public function completeActivity(ActivityEntity $activity): void {
    $this->changeActivityStatus($activity, 'Completed');
  }

  /**
   * @throws \API_Exception
   */
  public function changeActivityStatus(ActivityEntity $activity, string $status): void {
    $result = $this->api4->updateEntity(
      'Activity',
      $activity->getId(),
      ['status_id:name' => $status],
      ['checkPermissions' => FALSE],
    );

    $statusId = $result->single()['status_id'];
    $activity->setStatusId($statusId);
  }

}
