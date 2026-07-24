<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Upgrade;

use Civi\Api4\Activity;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Fixtures\ActivityFixture;
use Civi\Funding\Fixtures\ApplicationProcessBundleFixture;
use Civi\Funding\Fixtures\ApplicationSnapshotFixture;
use Civi\Funding\Fixtures\ContactFixture;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0024
 *
 * @group headless
 */
final class Upgrader0024Test extends AbstractFundingHeadlessTestCase {

  public function testExecute(): void {
    $contact = ContactFixture::addIndividual();
    $applicationProcessBundle = ApplicationProcessBundleFixture::create([
      'title' => 'Test Title',
      'identifier' => 'test_identifier',
    ]);
    $applicationProcessId = $applicationProcessBundle->getApplicationProcess()->getId();
    ApplicationSnapshotFixture::addFixture($applicationProcessId);
    $lastSnapshot = ApplicationSnapshotFixture::addFixture($applicationProcessId);

    ActivityFixture::addApplicationProcessFixture(
      $applicationProcessId,
      ActivityTypeNames::FUNDING_APPLICATION_STATUS_CHANGE,
      $contact['id'],
      [
        'created_date' => '2026-07-01 01:02:03',
        'modified_date' => '2026-07-01 01:02:03',
        'activity_date_time' => '2026-07-01 01:02:03',
        'funding_application_status_change.from_status' => 'eligible',
        'funding_application_status_change.to_status' => 'test',
      ]
    );
    $statusChangeActivity = ActivityFixture::addApplicationProcessFixture(
      $applicationProcessId,
      ActivityTypeNames::FUNDING_APPLICATION_STATUS_CHANGE,
      $contact['id'],
      [
        'created_date' => '2026-07-02 02:03:04',
        'modified_date' => '2026-07-02 02:03:04',
        'activity_date_time' => '2026-07-02 02:03:04',
        'funding_application_status_change.from_status' => 'eligible',
        'funding_application_status_change.to_status' => 'rework',
      ]
    );
    ActivityFixture::addApplicationProcessFixture(
      $applicationProcessId,
      ActivityTypeNames::FUNDING_APPLICATION_STATUS_CHANGE,
      $contact['id'],
      [
        'created_date' => '2026-07-03 04:05:06',
        'modified_date' => '2026-07-03 04:05:06',
        'activity_date_time' => '2026-07-03 04:05:06',
        'funding_application_status_change.from_status' => 'rework',
        'funding_application_status_change.to_status' => 'rework-review-requested',
      ]
    );

    /** @var \Civi\Funding\Upgrade\Upgrader0024 $upgrader */
    $upgrader = \Civi::service(Upgrader0024::class);

    $upgrader->execute(new \Log_null('test'));

    $activityGet = Activity::get(FALSE)
      ->addSelect(
        'subject',
        'details',
        'created_date',
        'funding_application_snapshot_creation.snapshot_id'
      )
      ->addWhere('activity_type_id:name', '=', ActivityTypeNames::FUNDING_APPLICATION_SNAPSHOT_CREATION);
    $activities = $activityGet->execute();
    static::assertCount(1, $activities);
    static::assertSame($lastSnapshot->getId(), $activities[0]['funding_application_snapshot_creation.snapshot_id']);
    // The last created date of a status change activity with a from_state that
    // is marked as snapshot required status.
    static::assertSame('2026-07-02 02:03:04', $activities[0]['created_date']);
    static::assertSame('Funding Application Snapshot Created', $activities[0]['subject']);
    static::assertSame(
      sprintf(
        'Application: Test Title (test_identifier)<br>'
        . 'Created during update for existing snapshot. Date (creation date of activity %d) may be inaccurate.',
        $statusChangeActivity['id']
      ),
      $activities[0]['details']
    );

    // Only one activity for each latest snapshot shall be created.
    $upgrader->execute(new \Log_null('test'));
    static::assertCount(1, $activityGet->execute());
  }

  public function testExecuteNoMatchingStatusChangeActivity(): void {
    $contact = ContactFixture::addIndividual();
    $applicationProcessBundle = ApplicationProcessBundleFixture::create([
      'title' => 'Test Title',
      'identifier' => 'test_identifier',
      'modification_date' => '2026-07-01 01:02:03',
    ]);
    $applicationProcessId = $applicationProcessBundle->getApplicationProcess()->getId();
    ApplicationSnapshotFixture::addFixture($applicationProcessId);
    $lastSnapshot = ApplicationSnapshotFixture::addFixture($applicationProcessId);

    ActivityFixture::addApplicationProcessFixture(
      $applicationProcessId,
      ActivityTypeNames::FUNDING_APPLICATION_STATUS_CHANGE,
      $contact['id'],
      [
        'created_date' => '2026-07-03 04:05:06',
        'modified_date' => '2026-07-03 04:05:06',
        'activity_date_time' => '2026-07-03 04:05:06',
        'funding_application_status_change.from_status' => 'rework',
        'funding_application_status_change.to_status' => 'rework-review-requested',
      ]
    );

    /** @var \Civi\Funding\Upgrade\Upgrader0024 $upgrader */
    $upgrader = \Civi::service(Upgrader0024::class);

    $upgrader->execute(new \Log_null('test'));

    $activityGet = Activity::get(FALSE)
      ->addSelect(
        'subject',
        'details',
        'created_date',
        'funding_application_snapshot_creation.snapshot_id'
      )
      ->addWhere('activity_type_id:name', '=', ActivityTypeNames::FUNDING_APPLICATION_SNAPSHOT_CREATION);
    $activities = $activityGet->execute();
    static::assertCount(1, $activities);
    static::assertSame($lastSnapshot->getId(), $activities[0]['funding_application_snapshot_creation.snapshot_id']);
    // The modification date of the application process.
    static::assertSame('2026-07-01 01:02:03', $activities[0]['created_date']);
    static::assertSame('Funding Application Snapshot Created', $activities[0]['subject']);
    static::assertSame(
      'Application: Test Title (test_identifier)<br>'
      . 'Created during update for existing snapshot. Date (modification date of application process)'
      . ' may be inaccurate.',
      $activities[0]['details']
    );

    // Only one activity for each latest snapshot shall be created.
    $upgrader->execute(new \Log_null('test'));
    static::assertCount(1, $activityGet->execute());
  }

}
