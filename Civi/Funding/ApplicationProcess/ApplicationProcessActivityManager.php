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
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\RemoteTools\Api4\Api4Interface;

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
      /** @phpstan-ignore-next-line */
      $values['status_id:name'] ??= 'Completed';
    }
    $createAction = Activity::create()
      ->setValues($values);

    /** @phpstan-var array{id: int}&array<string, mixed> $activityValues */
    $activityValues = $this->api4->executeAction($createAction)->first();
    $connectAction = EntityActivity::connect()
      ->setActivityId($activityValues['id'])
      ->setEntity(FundingApplicationProcess::_getEntityName())
      ->setEntityId($applicationProcess->getId());

    $this->api4->executeAction($connectAction);

    $getAction = Activity::get()
      ->addSelect('*', 'custom.*')
      ->addWhere('id', '=', $activityValues['id']);

    /** @phpstan-ignore-next-line */
    $activity->setValues($this->api4->executeAction($getAction)->first());
  }

  /**
   * @throws \API_Exception
   */
  public function deleteByApplicationProcess(int $applicationProcessId): void {
    foreach ($this->getByApplicationProcess($applicationProcessId) as $activity) {
      $action = Activity::delete()->addWhere('id', '=', $activity->getId());
      $this->api4->executeAction($action);
    }
  }

  /**
   * @phpstan-return array<ActivityEntity>
   *
   * @throws \API_Exception
   */
  public function getByApplicationProcess(int $applicationProcessId): array {
    $action = Activity::get()
      ->addSelect(
        '*',
        'custom.*',
      )->addJoin(
        FundingApplicationProcess::_getEntityName() . ' AS ap',
        'INNER',
        'EntityActivity',
        ['ap.id', '=' , $applicationProcessId]
      );

    return array_map(
      /** @phpstan-ignore-next-line */
      fn ($values) => ActivityEntity::fromArray($values),
      $this->api4->executeAction($action)->getArrayCopy()
    );
  }

}
