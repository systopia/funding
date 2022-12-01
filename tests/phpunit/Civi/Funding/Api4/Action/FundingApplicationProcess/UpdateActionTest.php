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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Util\SessionTestUtil;

/**
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\UpdateAction
 *
 * @group headless
 */
final class UpdateActionTest extends AbstractFundingHeadlessTestCase {

  public function test(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
    );

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'is_review_calculative' => NULL,
      'is_review_content' => NULL,
    ]);

    $applicationProcess
      ->setIsReviewCalculative(TRUE)
      ->setIsReviewContent(FALSE);

    $contactIdNoReview = ContactFixture::addIndividual()['id'];
    FundingCaseContactRelationFixture::addContact($contactIdNoReview, $fundingCase->getId(), ['test']);
    SessionTestUtil::mockInternalRequestSession($contactIdNoReview);

    $record = FundingApplicationProcess::update()
      ->setValues($applicationProcess->toArray())
      ->execute()
      ->first();
    static::assertNull($record['is_review_calculative']);
    static::assertNull($record['is_review_content']);

    $contactIdReviewCalculative = ContactFixture::addIndividual()['id'];
    FundingCaseContactRelationFixture::addContact(
      $contactIdReviewCalculative,
      $fundingCase->getId(),
      ['review_calculative']
    );
    SessionTestUtil::mockInternalRequestSession($contactIdReviewCalculative);

    $record = FundingApplicationProcess::update()
      ->setValues($applicationProcess->toArray())
      ->execute()
      ->first();
    static::assertTrue($record['is_review_calculative']);
    static::assertNull($record['is_review_content']);

    $contactIdReviewContent = ContactFixture::addIndividual()['id'];
    FundingCaseContactRelationFixture::addContact($contactIdReviewContent, $fundingCase->getId(), ['review_content']);
    SessionTestUtil::mockInternalRequestSession($contactIdReviewContent);

    $applicationProcess->setIsReviewCalculative(FALSE);
    $record = FundingApplicationProcess::update()
      ->setValues($applicationProcess->toArray())
      ->execute()
      ->first();
    static::assertTrue($record['is_review_calculative']);
    static::assertFalse($record['is_review_content']);
  }

}
