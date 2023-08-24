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

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\SaveAction
 *
 * @group headless
 */
final class SaveActionTest extends AbstractFundingHeadlessTestCase {

  public function test(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual(['first_name' => 'creation', 'last_name' => 'contact']);
    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'is_review_calculative' => NULL,
      'is_review_content' => NULL,
    ]);

    $contactIdNoReview = ContactFixture::addIndividual()['id'];
    FundingCaseContactRelationFixture::addContact($contactIdNoReview, $fundingCase->getId(), ['test']);
    RequestTestUtil::mockInternalRequest($contactIdNoReview);

    $applicationProcess->setIsReviewCalculative(TRUE);
    $e = NULL;
    try {
      FundingApplicationProcess::save()->addRecord($applicationProcess->toArray())->execute();
    }
    catch (UnauthorizedException $e) {
      static::assertSame('Permission to change calculative review result is missing.', $e->getMessage());
    }
    static::assertNotNull($e);

    $applicationProcess->setIsReviewCalculative(NULL);
    $applicationProcess->setIsReviewContent(TRUE);
    $e = NULL;
    try {
      FundingApplicationProcess::save()->addRecord($applicationProcess->toArray())->execute();
    }
    catch (UnauthorizedException $e) {
      static::assertSame('Permission to change content review result is missing.', $e->getMessage());
    }
    static::assertNotNull($e);

    $contactIdReviewCalculative = ContactFixture::addIndividual()['id'];
    FundingCaseContactRelationFixture::addContact(
      $contactIdReviewCalculative,
      $fundingCase->getId(),
      ['review_calculative']
    );
    RequestTestUtil::mockInternalRequest($contactIdReviewCalculative);

    $applicationProcess->setIsReviewCalculative(TRUE);
    $applicationProcess->setIsReviewContent(NULL);
    $record = FundingApplicationProcess::save()
      ->addRecord($applicationProcess->toArray())
      ->execute()
      ->first();
    static::assertTrue($record['is_review_calculative']);

    $contactIdReviewContent = ContactFixture::addIndividual()['id'];
    FundingCaseContactRelationFixture::addContact($contactIdReviewContent, $fundingCase->getId(), ['review_content']);
    RequestTestUtil::mockInternalRequest($contactIdReviewContent);

    $applicationProcess->setIsReviewContent(FALSE);
    $record = FundingApplicationProcess::save()
      ->addRecord($applicationProcess->toArray())
      ->execute()
      ->first();
    static::assertTrue($record['is_review_calculative']);
    static::assertFalse($record['is_review_content']);
  }

}
