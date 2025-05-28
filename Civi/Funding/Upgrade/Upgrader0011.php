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

namespace Civi\Funding\Upgrade;

use Civi\Api4\Activity;
use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\EntityActivity;
use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\OptionGroup;
use Civi\Api4\OptionValue;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;

final class Upgrader0011 implements UpgraderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function execute(\Log $log): void {
    $log->info('Migrate tasks');
    $this->migrateTasks();
    $log->info('Create clearing process entities');
    $this->createClearingProcesses();
  }

  /**
   * External tasks weren't used in any way so we delete existing ones.
   *
   * @throws \CRM_Core_Exception
   */
  private function deleteExternalTasks(): void {
    $this->api4->deleteEntities(
      Activity::getEntityName(),
      Comparison::new('activity_type_id:name', '=', 'funding_application_task_external')
    );

    $this->api4->deleteEntities(OptionValue::getEntityName(), CompositeCondition::fromFieldValuePairs([
      'name' => 'funding_application_task_external',
      'option_group_id.name' => 'activity_type',
    ]));
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function migrateTasks(): void {
    $this->deleteExternalTasks();
    $this->migrateInternalTasks();
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function migrateInternalTasks(): void {
    $action = Activity::get(FALSE)
      ->addJoin(FundingApplicationProcess::getEntityName() . ' AS ap', 'INNER', 'EntityActivity')
      ->addSelect(
        '*',
        'funding_application_task.type',
        'ap.id',
        'ap.funding_case_id',
      )
      ->addWhere('activity_type_id:name', '=', 'funding_application_task_internal');

    $tasks = ActivityEntity::allFromApiResult($this->api4->executeAction($action));
    foreach ($tasks as $task) {
      $this->api4->updateEntity(Activity::getEntityName(), $task->getId(), [
        'activity_type_id:name' => ActivityTypeNames::APPLICATION_PROCESS_TASK,
        // Possible types: 'review_calculative' and 'review_content'. These are
        // used again in the abstract application task handlers. However, a
        // funding case type that don't have handlers that take care of these
        // tasks will result in tasks that won't be changed automatically.
        'funding_case_task.type' => $task->get('funding_application_task.type'),
        'funding_case_task.funding_case_id' => $task->get('ap.funding_case_id'),
        'funding_application_process_task.application_process_id' => $task->get('ap.id'),
      ]);

      EntityActivity::disconnectActivity(FALSE)
        ->setActivityId($task->getId())
        ->execute();
    }

    $this->api4->deleteEntities(
      CustomField::getEntityName(),
      Comparison::new('custom_group_id.name', '=', 'funding_application_task')
    );

    $this->api4->deleteEntities(
      CustomGroup::getEntityName(),
      Comparison::new('name', '=', 'funding_application_task')
    );

    $this->api4->deleteEntities(
      OptionGroup::getEntityName(),
      Comparison::new('name', '=', 'funding_application_task_type')
    );

    $this->api4->deleteEntities(OptionValue::getEntityName(), CompositeCondition::fromFieldValuePairs([
      'name' => 'funding_application_task_internal',
      'option_group_id.name' => 'activity_type',
    ]));
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createClearingProcesses(): void {
    /** @phpstan-var list<int> $ids */
    $ids = $this->api4->execute(FundingApplicationProcess::getEntityName(), 'get', [
      'select' => ['id'],
      'join' => [
        ['FundingClearingProcess AS cp', 'EXCLUDE', ['cp.application_process_id', '=', 'id']],
      ],
    ])->column('id');

    foreach ($ids as $id) {
      $clearingProcess = ClearingProcessEntity::fromArray([
        'application_process_id' => $id,
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
      $this->api4->createEntity(FundingClearingProcess::getEntityName(), $clearingProcess->toArray());
    }
  }

}
