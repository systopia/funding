<?php

declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\AttachmentFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;
use Civi\Funding\Util\RequestTestUtil;
use CRM_Core_DAO;
use CRM_Funding_ExtensionUtil as E;
use Throwable;

/**
 * @covers \Civi\Api4\FundingAmountApprovedChangeRequest
 * @covers \Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\GetAction
 *
 * @group headless
 */
final class FundingAmountApprovedChangeRequestTest extends AbstractFundingHeadlessTestCase {

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id']
    );

    $request = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 100.0)
      ->execute()->first();

    $result = FundingAmountApprovedChangeRequest::get(FALSE)
      ->addSelect('id', 'amount_requested', 'status')
      ->execute();

    static::assertCount(1, $result);
    static::assertEquals(
      [
        'id' => $request['id'],
        'amount_requested' => 100.0,
        'status' => 'new',
      ],
      $result->first()
    );
  }

  public function testGetCanReview(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
      [
        'status' => 'ongoing',
        'amount_approved' => 50.0,
      ]
    );
    PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 120.0)
      ->execute()->first();

    $result = FundingAmountApprovedChangeRequest::get()
      ->addSelect('id', 'CAN_review')
      ->execute();

    static::assertCount(1, $result);
    static::assertSame(
      [
        'id' => $request['id'],
        'CAN_review' => TRUE,
      ],
      $result->first()
    );
  }

  public function testGetCanReviewNonNewStatus(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
      [
        'status' => 'ongoing',
        'amount_approved' => 50.0,
      ]
    );
    PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'approved')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 120.0)
      ->execute()->first();

    $result = FundingAmountApprovedChangeRequest::get()
      ->addSelect('id', 'CAN_review')
      ->execute();

    static::assertCount(1, $result);
    static::assertSame(
      [
        'id' => $request['id'],
        'CAN_review' => FALSE,
      ],
      $result->first()
    );
  }

  public function testGetCanReviewNotPermitted(): void {
    $contactNotPermitted = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
      [
        'status' => 'ongoing',
        'amount_approved' => 50.0,
      ]
    );
    PayoutProcessFixture::addFixture($fundingCase->getId());

    RequestTestUtil::mockInternalRequest($contactNotPermitted['id']);

    FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $creationContact['id'])
      ->addValue('amount_requested', 120.0)
      ->execute();

    $result = FundingAmountApprovedChangeRequest::get()
      ->addSelect('id', 'CAN_review')
      ->execute();

    static::assertCount(0, $result);
  }

  public function testCreateAndGet(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );

    $result = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 100.0)
      ->execute();

    static::assertCount(1, $result);
    $request = $result->first();
    static::assertEquals($fundingCase->getId(), $request['funding_case_id']);
    static::assertEquals('new', $request['status']);
    static::assertEquals(100.0, $request['amount_requested']);
  }

  public function testApprove(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
      [
        'status' => 'ongoing',
        'amount_approved' => 50.0,
      ]
    );
    PayoutProcessFixture::addFixture($fundingCase->getId());

    AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE]
    );

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 120.0)
      ->execute()->first();

    $result = FundingAmountApprovedChangeRequest::approve(FALSE)
      ->setIds([$request['id']])
      ->execute();

    static::assertCount(1, $result);
    static::assertEquals('approved', $result->first()['status']);
    static::assertEquals(120.0, $result->first()['amount_approved']);

    $updatedCase = FundingCase::get(FALSE)
      ->addWhere('id', '=', $fundingCase->getId())
      ->execute()->first();
    static::assertEquals(120.0, $updatedCase['amount_approved']);
  }

  public function testApprovePartial(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
      [
        'status' => 'ongoing',
        'amount_approved' => 50.0,
      ]
    );
    PayoutProcessFixture::addFixture($fundingCase->getId());

    AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE]
    );

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 120.0)
      ->execute()->first();

    $result = FundingAmountApprovedChangeRequest::approvePartial(FALSE)
      ->setAmountApproved(80.0)
      ->setIds([$request['id']])
      ->execute();

    static::assertCount(1, $result);
    static::assertEquals('approved_partial', $result->first()['status']);
    static::assertEquals(80.0, $result->first()['amount_approved']);

    $updatedCase = FundingCase::get(FALSE)
      ->addWhere('id', '=', $fundingCase->getId())
      ->execute()->first();
    static::assertEquals(80.0, $updatedCase['amount_approved']);
  }

  public function testReject(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
      [
        'status' => 'ongoing',
        'amount_approved' => 50.0,
      ]
    );

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 120.0)
      ->execute()->first();

    $result = FundingAmountApprovedChangeRequest::reject(FALSE)
      ->setIds([$request['id']])
      ->execute();

    static::assertCount(1, $result);
    static::assertEquals('rejected', $result->first()['status']);

    $updatedCase = FundingCase::get(FALSE)
      ->addWhere('id', '=', $fundingCase->getId())
      ->execute()->first();
    static::assertEquals(50.0, $updatedCase['amount_approved']);
  }

  public function testApproveNonExistentFundingCase(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id']
    );

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 120.0)
      ->execute()->first();

    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS = 0');
    FundingAmountApprovedChangeRequest::update(FALSE)
      ->addWhere('id', '=', $request['id'])
      ->setValues(['funding_case_id' => 999999])
      ->execute();
    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS = 1');

    $this->expectException(Throwable::class);

    FundingAmountApprovedChangeRequest::approve(FALSE)
      ->setIds([$request['id']])
      ->execute();
  }

}
