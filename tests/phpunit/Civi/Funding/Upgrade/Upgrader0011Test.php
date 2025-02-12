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
use Civi\Api4\FundingCase;
use Civi\Api4\FundingCaseType;
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\DAODeleteAction;
use Civi\Api4\OptionValue;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Fixtures\ApplicationProcessBundleFixture;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\Fixtures\ContactFixture;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0011
 * @group headless
 */
final class Upgrader0011Test extends AbstractFundingHeadlessTestCase {

  use ArraySubsetAsserts;

  protected function setUp(): void {
    parent::setUp();
  }

  public function testExecute(): void {

    // Creating custom groups and custom fields changes the DB schema and thus commits the transaction.
    \Civi\Core\Transaction\Manager::singleton()->forceRollback();

    try {
      CustomGroup::create(FALSE)
        ->setValues([
          'name' => 'funding_application_task',
          'table_name' => 'civicrm_value_funding_application_task',
          'title' => 'Funding Application Task',
          'extends' => 'Activity',
          'extends_entity_column_value:name' => [
            'funding_application_task_internal',
          ],
        ])->execute()->single();

      CustomField::create(FALSE)->setValues([
        'custom_group_id.name' => 'funding_application_task',
        'name' => 'type',
        'label' => 'Type',
        'data_type' => 'String',
        'html_type' => 'Text',
        'column_name' => 'type',
      ])->execute()->single();

      \Civi\Core\Transaction\Manager::singleton()->inc();
      \Civi\Core\Transaction\Manager::singleton()->getFrame()->setRollbackOnly();

      OptionValue::create(FALSE)
        ->setValues([
          'option_group_id.name' => 'activity_type',
          'label' => 'External Funding Application Task',
          'name' => 'funding_application_task_external',
        ])->execute();

      OptionValue::create(FALSE)
        ->setValues([
          'option_group_id.name' => 'activity_type',
          'label' => 'Internal Funding Application Task',
          'name' => 'funding_application_task_internal',
        ])->execute();

      $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);

      (new DAODeleteAction(FundingProgram::getEntityName(), 'delete'))
        ->setCheckPermissions(FALSE)
        ->addWhere('id', 'IS NOT NULL')
        ->execute();

      $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ACCESS_FUNDING]);

      $contact = ContactFixture::addIndividual();
      $applicationProcessBundle = ApplicationProcessBundleFixture::create();
      $applicationProcessId = $applicationProcessBundle->getApplicationProcess()->getId();
      $externalTask = $this->addExternalTask($contact['id'], $applicationProcessId);
      $internalTask = $this->addInternalTask($contact['id'], $applicationProcessId);

      $clearingProcessBundle = ClearingProcessBundleFixture::create();

      /** @var \Civi\Funding\Upgrade\Upgrader0011 $upgrader */
      $upgrader = \Civi::service(Upgrader0011::class);
      $upgrader->execute(new \Log_null('test'));

      // New clearing process created.
      static::assertArraySubset([
        'status' => 'not-started',
        'creation_date' => NULL,
        'modification_date' => NULL,
        'report_data' => [],
        'is_review_content' => NULL,
        'reviewer_cont_contact_id' => NULL,
        'is_review_calculative' => NULL,
        'reviewer_calc_contact_id' => NULL,
      ], FundingClearingProcess::get(FALSE)
        ->addWhere('application_process_id', '=', $applicationProcessId)
        ->execute()
        ->single()
      );

      // Existing clearing process unchanged.
      static::assertEquals(
        $clearingProcessBundle->getClearingProcess()->toArray(),
        FundingClearingProcess::get(FALSE)
          ->addWhere('id', '=', $clearingProcessBundle->getClearingProcess()->getId())
          ->execute()
          ->single()
      );

      // Task migration.
      static::assertCount(0, Activity::get(FALSE)
        ->addWhere('id', '=', $externalTask['id'])
        ->execute()
      );

      $internalTaskUpdated = Activity::get(FALSE)
        ->addSelect('*', 'custom.*', 'activity_type_id:name')
        ->addWhere('id', '=', $internalTask['id'])
        ->execute()
        ->single();

      static::assertArraySubset([
        'activity_type_id:name' => ActivityTypeNames::APPLICATION_PROCESS_TASK,
        'funding_case_task.type' => 'test',
        'funding_case_task.funding_case_id' => $applicationProcessBundle->getFundingCase()->getId(),
        'funding_application_process_task.application_process_id' => $applicationProcessId,
      ], $internalTaskUpdated);

      static::assertCount(0, EntityActivity::get(FALSE)->execute());

      static::assertCount(0, CustomGroup::get(FALSE)
        ->addWhere('name', '=', 'funding_application_task')
        ->execute()
      );

      static::assertCount(0, OptionValue::get(FALSE)
        ->addWhere('name', 'IN', ['funding_application_task_external', 'funding_application_task_internal'])
        ->addWhere('option_group_id.name', '=', 'activity_type')
        ->execute());
    } finally {
      \Civi\Core\Transaction\Manager::singleton()->forceRollback();

      // Because the upgrader deletes custom fields and custom groups and
      // thereby commits the open transaction we delete everything explicitly.
      $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);
      (new DAODeleteAction(FundingApplicationProcess::getEntityName(), 'delete'))
        ->setCheckPermissions(FALSE)
        ->addWhere('id', 'IS NOT NULL')
        ->execute();
      FundingCase::delete(FALSE)
        ->addWhere('id', 'IS NOT NULL')
        ->execute();
      FundingProgram::delete(FALSE)
        ->addWhere('id', 'IS NOT NULL')
        ->execute();
      FundingCaseType::delete(FALSE)
        ->addWhere('name', '=', 'TestCaseType')
        ->execute();

      OptionValue::delete(FALSE)
        ->addWhere('name', 'IN', ['funding_application_task_external', 'funding_application_task_internal'])
        ->addWhere('option_group_id.name', '=', 'activity_type')
        ->execute();

      CustomField::delete(FALSE)
        ->addWhere('custom_group_id.name', '=', 'funding_application_task')
        ->execute();
      CustomGroup::delete(FALSE)
        ->addWhere('name', '=', 'funding_application_task')
        ->execute();

      // There needs to be an open transaction to prevent an error when the CiviCRM test listener tries to rollback a
      // transaction.
      \Civi\Core\Transaction\Manager::singleton()->inc();
      \Civi\Core\Transaction\Manager::singleton()->getFrame()->setRollbackOnly();
    }
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  private function addExternalTask(int $sourceContactId, int $applicationProcessId): array {
    return $this->addTask('funding_application_task_external', $sourceContactId, $applicationProcessId);
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  private function addInternalTask(int $sourceContactId, int $applicationProcessId): array {
    return $this->addTask('funding_application_task_internal', $sourceContactId, $applicationProcessId);
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  private function addTask(string $activityTypeName, int $sourceContactId, int $applicationProcessId): array {
    $activity = Activity::create(FALSE)
      ->setValues([
        'source_contact_id' => $sourceContactId,
        'activity_type_id:name' => $activityTypeName,
        'status_id:name' => 'Completed',
        'subject' => 'Test',
        'funding_application_task.type' => 'test',
      ])->execute()->single();

    EntityActivity::connect(FALSE)
      ->setActivityId($activity['id'])
      ->setEntity(FundingApplicationProcess::getEntityName())
      ->setEntityId($applicationProcessId)
      ->execute();

    return $activity;
  }

}
