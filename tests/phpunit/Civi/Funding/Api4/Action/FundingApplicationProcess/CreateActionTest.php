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
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Util\SessionTestUtil;

/**
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\CreateAction
 *
 * @group headless
 */
final class CreateActionTest extends AbstractFundingHeadlessTestCase {

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

    $contactIdNoReview = ContactFixture::addIndividual()['id'];
    FundingCaseContactRelationFixture::addContact($contactIdNoReview, $fundingCase->getId(), ['test']);
    SessionTestUtil::mockInternalRequestSession($contactIdNoReview);

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'id' => NULL,
      'funding_case_id' => $fundingCase->getId(),
    ])
      ->setTitle('Test 1')
      ->setIdentifier('test1');

    $applicationProcess->setIsReviewCalculative(TRUE);
    $e = NULL;
    try {
      FundingApplicationProcess::create()->setValues($applicationProcess->toArray())->execute();
    }
    catch (UnauthorizedException $e) {
      static::assertSame('Permission to change calculative review result is missing.', $e->getMessage());
    }
    static::assertNotNull($e);

    $applicationProcess->setIsReviewCalculative(NULL);
    $applicationProcess->setIsReviewContent(TRUE);
    $e = NULL;
    try {
      FundingApplicationProcess::create()->setValues($applicationProcess->toArray())->execute();
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
    SessionTestUtil::mockInternalRequestSession($contactIdReviewCalculative);

    $applicationProcess->setIsReviewCalculative(TRUE);
    $applicationProcess->setIsReviewContent(NULL);
    $record = FundingApplicationProcess::create()
      ->setValues($applicationProcess->toArray())
      ->execute()
      ->first();
    static::assertTrue($record['is_review_calculative']);

    $contactIdReviewContent = ContactFixture::addIndividual()['id'];
    FundingCaseContactRelationFixture::addContact($contactIdReviewContent, $fundingCase->getId(), ['review_content']);
    SessionTestUtil::mockInternalRequestSession($contactIdReviewContent);

    $applicationProcess->setTitle('Test 2');
    $applicationProcess->setIdentifier('test2');
    $applicationProcess->setIsReviewCalculative(NULL);
    $applicationProcess->setIsReviewContent(FALSE);
    $record = FundingApplicationProcess::create()
      ->setValues($applicationProcess->toArray())
      ->execute()
      ->first();
    static::assertFalse($record['is_review_content']);
  }

}
