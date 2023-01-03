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

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ActivityTypeIds;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Util\SessionTestUtil;
use Civi\RemoteTools\Api4\Api4;

/**
 * @group headless
 *
 * @covers \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager
 */
final class ApplicationProcessActivityManagerTest extends AbstractFundingHeadlessTestCase {

  private ApplicationProcessActivityManager $activityManager;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManager = new ApplicationProcessActivityManager(Api4::getInstance());
  }

  public function test(): void {
    $recipientContact = ContactFixture::addOrganization(['display_name' => 'Test']);
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $creationContact = ContactFixture::addIndividual(['first_name' => 'creation', 'last_name' => 'contact']);
    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );
    FundingCaseContactRelationFixture::addContact($recipientContact['id'], $fundingCase->getId(), ['perm']);
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'title' => 'Foo',
      'identifier' => '22-bar',
      'status' => 'draft',
    ]);

    $activity = ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_STATUS_CHANGE,
      'subject' => 'Test subject',
      'details' => 'Test details',
      'funding_application_status_change.from_status' => 'old-status',
      'funding_application_status_change.to_status' => 'new-status',
    ]);
    $this->activityManager->addActivity($recipientContact['id'], $applicationProcess, $activity);

    static::assertSame($fundingCase->getId(), $activity->getSourceRecordId());
    static::assertSame('Test subject', $activity->getSubject());
    static::assertSame('Test details', $activity->getDetails());
    static::assertSame(ActivityTypeIds::FUNDING_APPLICATION_STATUS_CHANGE, $activity->getActivityTypeId());
    // "Completed"
    static::assertSame(2, $activity->getStatusId());
    static::assertSame('old-status', $activity->get('funding_application_status_change.from_status'));
    static::assertSame('new-status', $activity->get('funding_application_status_change.to_status'));

    SessionTestUtil::mockInternalRequestSession($recipientContact['id']);
    $activities = $this->activityManager->getByApplicationProcess($applicationProcess->getId());
    static::assertCount(1, $activities);
    static::assertEquals($activity->toArray() + [
      'activity_type_id:name' => 'funding_application_status_change',
      'source_contact_name' => 'Test',
      'from_status' => 'old-status',
      'to_status' => 'new-status',
    ], $activities[0]->toArray()
    );

    $this->activityManager->deleteByApplicationProcess($applicationProcess->getId());
    static::assertCount(0, $this->activityManager->getByApplicationProcess($applicationProcess->getId()));
  }

}
