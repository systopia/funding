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

namespace Civi\Api4;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ApplicationSnapshotFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Form\SonstigeAktivitaet\JsonSchema\AVK1JsonSchema;
use Civi\Funding\Mock\Form\FundingCaseType\TestJsonSchemaFactory;
use Civi\Funding\Util\SessionTestUtil;

/**
 * @covers \Civi\Api4\FundingApplicationProcess
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\DeleteAction
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\GetAction
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\GetFieldsAction
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\GetFormDataAction
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\GetJsonSchemaAction
 *
 * @group headless
 */
final class FundingApplicationProcessTest extends AbstractFundingHeadlessTestCase {

  /**
   * @phpstan-ignore-next-line
   */
  private FundingProgramEntity $fundingProgram;

  public function testDelete(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getFundingProgramId(),
      ['application_create'],
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['application_withdraw']);

    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $result = FundingApplicationProcess::delete()->addWhere('id', '=', $applicationProcess->getId())->execute();
    static::assertCount(1, $result);
    static::assertSame(['id' => $applicationProcess->getId()], $result->first());
  }

  public function testDeleteMissingPermission(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getFundingProgramId(),
      ['application_create'],
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['application_permission']);

    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to delete application is missing.');

    FundingApplicationProcess::delete()->addWhere('id', '=', $applicationProcess->getId())->execute();
  }

  public function testDeleteWithoutAnyPermission(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    // Contact does not now that application process exists without any permission.
    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $result = FundingApplicationProcess::delete()->addWhere('id', '=', $applicationProcess->getId())->execute();
    static::assertCount(0, $result);
  }

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $contactNotPermitted = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['application_permission']);

    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $result = FundingApplicationProcess::get()->addSelect('id')->execute();
    static::assertCount(1, $result);
    static::assertSame(['id' => $applicationProcess->getId()], $result->first());

    SessionTestUtil::mockRemoteRequestSession((string) $contactNotPermitted['id']);
    static::assertCount(0, FundingApplicationProcess::get()
      ->addSelect('id')->execute());
  }

  public function testGetFieldsAction(): void {
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    $contactReviewCalculative = ContactFixture::addIndividual([
      'first_name' => 'Calculative',
      'last_name' => 'Reviewer',
    ]);
    FundingCaseContactRelationFixture::addContact(
      $contactReviewCalculative['id'],
      $fundingCase->getId(),
      ['review_calculative'],
    );
    $contactReviewContent = ContactFixture::addIndividual(['first_name' => 'Content', 'last_name' => 'Reviewer']);
    FundingCaseContactRelationFixture::addContact(
      $contactReviewContent['id'],
      $fundingCase->getId(),
      ['review_content']
    );

    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['some_permission']);

    $contactNotPermitted = ContactFixture::addIndividual();

    SessionTestUtil::mockInternalRequestSession($contact['id']);
    // No load options.
    $result = FundingApplicationProcess::getFields()->execute()->indexBy('name');
    static::assertFalse($result['reviewer_calc_contact_id']['options']);
    static::assertFalse($result['reviewer_cont_contact_id']['options']);

    // Load options without application process ID.
    $result = FundingApplicationProcess::getFields()->setLoadOptions(TRUE)->execute()->indexBy('name');
    static::assertTrue($result['reviewer_calc_contact_id']['options']);
    static::assertTrue($result['reviewer_cont_contact_id']['options']);

    // Load options with unknown application process ID.
    $result = FundingApplicationProcess::getFields()
      ->setLoadOptions(TRUE)
      ->addValue('id', $applicationProcess->getId() + 1)
      ->execute()
      ->indexBy('name');
    static::assertTrue($result['reviewer_calc_contact_id']['options']);
    static::assertTrue($result['reviewer_cont_contact_id']['options']);

    // Load options with known application process ID.
    $result = FundingApplicationProcess::getFields()
      ->setLoadOptions(TRUE)
      ->addValue('id', $applicationProcess->getId())
      ->execute()
      ->indexBy('name');
    $expectedReviewersCalculative = [$contactReviewCalculative['id'] => 'Calculative Reviewer'];
    $expectedReviewersContent = [$contactReviewContent['id'] => 'Content Reviewer'];
    static::assertSame($expectedReviewersCalculative, $result['reviewer_calc_contact_id']['options']);
    static::assertSame($expectedReviewersContent, $result['reviewer_cont_contact_id']['options']);

    // Load options without application process permission.
    SessionTestUtil::mockInternalRequestSession($contactNotPermitted['id']);
    $result = FundingApplicationProcess::getFields()
      ->setLoadOptions(TRUE)
      ->addValue('id', $applicationProcess->getId())
      ->execute()
      ->indexBy('name');
    static::assertTrue($result['reviewer_calc_contact_id']['options']);
    static::assertTrue($result['reviewer_cont_contact_id']['options']);
  }

  public function testGetFormData(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'start_date' => '2022-11-15',
      'end_date' => '2022-11-16',
    ]);

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['review_permission']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['review_permission']);
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $result = FundingApplicationProcess::getFormData()
      ->setId($applicationProcess->getId())
      ->execute();

    static::assertIsArray($result['data']);
    static::assertSame('2022-11-15', $result['data']['beginn']);
  }

  public function testGetJsonSchema(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['review_permission']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['review_permission']);
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $result = FundingApplicationProcess::getJsonSchema()
      ->setId($applicationProcess->getId())
      ->execute();

    static::assertInstanceOf(AVK1JsonSchema::class, $result['jsonSchema']);
  }

  public function testSubmitForm(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'start_date' => '2022-11-15',
      'end_date' => '2022-11-16',
    ]);

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['review_permission']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['review_permission']);
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $result = FundingApplicationProcess::submitForm()
      ->setId($applicationProcess->getId())
      ->setData(['action' => 'test'])
      ->execute();

    static::assertIsArray($result['errors']);
    static::assertNotEmpty($result['errors']['/action']);
    static::assertNotEmpty($result['data']);
  }

  public function testSubmitFormApprove(): void {
    // This test will create an application snapshot.
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase(TestJsonSchemaFactory::getSupportedFundingCaseTypes()[0]);

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['view']
    );
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_content', 'review_calculative']
    );
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'status' => 'review',
      'is_review_content' => TRUE,
      'is_review_calculative' => TRUE,
      'start_date' => '2022-11-15',
      'end_date' => '2022-11-16',
      'request_data' => ['amountRequested' => 10, 'resources' => 20],
    ]);

    $result = FundingApplicationProcess::submitForm()
      ->setId($applicationProcess->getId())
      ->setData([
        'applicationProcessId' => $applicationProcess->getId(),
        'action' => 'approve',
        'title' => 'Title',
        'recipient' => $fundingCase->getRecipientContactId(),
        'startDate' => '2022-11-15',
        'endDate' => '2022-11-16',
        'amountRequested' => 10,
        'resources' => 20,
      ])
      ->execute();

    static::assertEquals(new \stdClass(), $result['errors']);
    static::assertNotEmpty($result['data']);
  }

  public function testSubmitFormWithdrawChange(): void {
    // This test will perform an application restore.
    // Reviewer contact required to set review flags.
    $reviewerContact = ContactFixture::addIndividual();
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase(TestJsonSchemaFactory::getSupportedFundingCaseTypes()[0]);

    FundingProgramContactRelationFixture::addContact(
      $reviewerContact['id'],
      $this->fundingProgram->getId(),
      ['view']
    );
    FundingCaseContactRelationFixture::addContact(
      $reviewerContact['id'],
      $fundingCase->getId(),
      ['review_content', 'review_calculative']
    );

    SessionTestUtil::mockInternalRequestSession($reviewerContact['id']);
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'status' => 'rework',
      'is_review_content' => TRUE,
      'is_review_calculative' => TRUE,
      'start_date' => '2022-11-15',
      'end_date' => '2022-11-16',
      'request_data' => ['amountRequested' => 10, 'resources' => 20],
    ]);

    ApplicationSnapshotFixture::addFixture($applicationProcess->getId(), [
      'amount_requested' => 11,
      'request_data' => ['amountRequested' => 11, 'resources' => 22],
    ]);

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['application_create']
    );
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['application_withdraw']
    );

    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $result = FundingApplicationProcess::submitForm()
      ->setId($applicationProcess->getId())
      ->setData([
        'applicationProcessId' => $applicationProcess->getId(),
        'action' => 'withdraw-change',
        'title' => 'Title',
        'recipient' => $fundingCase->getRecipientContactId(),
        'startDate' => '2022-11-15',
        'endDate' => '2022-11-16',
        'amountRequested' => 10,
        'resources' => 20,
      ])
      ->execute();

    static::assertEquals(new \stdClass(), $result['errors']);
    static::assertNotEmpty($result['data']);
    static::assertSame(11, $result['data']['amountRequested']);
    static::assertSame(22, $result['data']['resources']);
  }

  public function testValidateForm(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'start_date' => '2022-11-15',
      'end_date' => '2022-11-16',
    ]);

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['review_permission']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['review_permission']);
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $result = FundingApplicationProcess::validateForm()
      ->setId($applicationProcess->getId())
      ->setData(['action' => 'test'])
      ->execute();

    static::assertFalse($result['valid']);
    static::assertIsArray($result['errors']);
    static::assertNotEmpty($result['errors']['/action']);
    static::assertNotEmpty($result['data']);
  }

  private function createFundingCase(string $name = 'AVK1SonstigeAktivitaet'): FundingCaseEntity {
    $this->fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture([
      'name' => $name,
    ]);
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual(['first_name' => 'creation', 'last_name' => 'contact']);

    return FundingCaseFixture::addFixture(
      $this->fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );
  }

}
