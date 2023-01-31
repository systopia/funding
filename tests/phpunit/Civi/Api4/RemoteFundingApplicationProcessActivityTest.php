<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Api4;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ActivityTypeIds;
use Civi\Funding\Fixtures\ActivityFixture;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\PHPUnit\Traits\ArrayAssertTrait;

/**
 * @covers \Civi\Api4\RemoteFundingApplicationProcessActivity
 * @covers \Civi\Funding\EventSubscriber\Remote\ApplicationProcessActivityGetSubscriber
 *
 * @group headless
 */
final class RemoteFundingApplicationProcessActivityTest extends AbstractFundingHeadlessTestCase {

  use ArrayAssertTrait;

  public function testGet(): void {
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual(['first_name' => 'creation', 'last_name' => 'contact']);
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );
    $applicationProcess = ApplicationProcessFixture::addFixture(
      $fundingCase->getId(),
      ['start_date' => '2022-09-20 20:20:20']
    );

    $contact = ContactFixture::addIndividual();
    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $fundingProgram->getId(),
      ['application_program_perm']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['application_case_perm']);

    $createActivity = ActivityFixture::addApplicationProcessFixture(
      $applicationProcess->getId(),
      ActivityTypeIds::FUNDING_APPLICATION_CREATE,
      $creationContact['id']
    );

    $statusChangeActivity = ActivityFixture::addApplicationProcessFixture(
      $applicationProcess->getId(),
      ActivityTypeIds::FUNDING_APPLICATION_STATUS_CHANGE,
      $creationContact['id']
    );

    $externalCommentActivity = ActivityFixture::addApplicationProcessFixture(
      $applicationProcess->getId(),
      ActivityTypeIds::FUNDING_APPLICATION_COMMENT_EXTERNAL,
      $contact['id']
    );

    ActivityFixture::addApplicationProcessFixture(
      $applicationProcess->getId(),
      ActivityTypeIds::FUNDING_APPLICATION_COMMENT_INTERNAL,
      $contact['id']
    );

    ActivityFixture::addApplicationProcessFixture(
      $applicationProcess->getId(),
      ActivityTypeIds::FUNDING_APPLICATION_REVIEW_STATUS_CHANGE,
      $contact['id']
    );

    $action = RemoteFundingApplicationProcessActivity::get()
      ->setApplicationProcessId($applicationProcess->getId())
      ->setRemoteContactId((string) $contact['id']);

    $activities = $action->execute();
    static::assertArrayHasSameValues([
      $createActivity['id'],
      $statusChangeActivity['id'],
      $externalCommentActivity['id'],
    ], $activities->column('id'));
  }

}
