<?php

declare(strict_types = 1);

namespace Civi\Api4;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\AttachmentFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingAmountApprovedChangeRequestFixture;
use Civi\Funding\Fixtures\FundingCaseBundleFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;
use Civi\Funding\Util\RequestTestUtil;
use CRM_Core_DAO;
use CRM_Funding_ExtensionUtil as E;

/**
 * @covers \Civi\Api4\FundingAmountApprovedChangeRequest
 * @covers \Civi\Funding\Api4\Action\FundingAmountApprovedChangeRequest\GetAction
 *
 * @group headless
 */
final class FundingAmountApprovedChangeRequestTest extends AbstractFundingHeadlessTestCase {

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id'],
      ['amount_requested' => 100.0]
    );

    $result = FundingAmountApprovedChangeRequest::get(FALSE)
      ->addSelect('id', 'amount_requested', 'status')
      ->execute();

    static::assertCount(1, $result);
    static::assertSame(
      [
        'id' => $request->getId(),
        'amount_requested' => 100.0,
        'status' => 'new',
      ],
      $result->first()
    );
  }

  public function testGetCanReview(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create([
      'status' => 'ongoing',
      'amount_approved' => 50.0,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['view', 'review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $result = FundingAmountApprovedChangeRequest::get()
      ->addSelect('id', 'CAN_review')
      ->execute();

    static::assertCount(1, $result);
    static::assertSame(
      [
        'id' => $request->getId(),
        'CAN_review' => TRUE,
      ],
      $result->first()
    );
  }

  public function testGetCanReviewNonNewStatus(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create([
      'status' => 'ongoing',
      'amount_approved' => 50.0,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id'],
      ['status' => 'approved']
    );

    $result = FundingAmountApprovedChangeRequest::get()
      ->addSelect('id', 'CAN_review')
      ->execute();

    static::assertCount(1, $result);
    static::assertSame(
      [
        'id' => $request->getId(),
        'CAN_review' => FALSE,
      ],
      $result->first()
    );
  }

  public function testGetCanReviewNotPermitted(): void {
    $contactNotPermitted = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create([
      'status' => 'ongoing',
      'amount_approved' => 50.0,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    PayoutProcessFixture::addFixture($fundingCase->getId());

    RequestTestUtil::mockInternalRequest($contactNotPermitted['id']);

    FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $fundingCase->getCreationContactId()
    );

    $result = FundingAmountApprovedChangeRequest::get()
      ->addSelect('id', 'CAN_review')
      ->execute();

    static::assertCount(0, $result);
  }

  public function testCreateAndGet(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();

    $result = FundingAmountApprovedChangeRequest::create(FALSE)
      ->addValue('funding_case_id', $fundingCase->getId())
      ->addValue('status', 'new')
      ->addValue('creation_date', date('Y-m-d H:i:s'))
      ->addValue('creation_contact_id', $contact['id'])
      ->addValue('amount_requested', 100.0)
      ->execute();

    static::assertCount(1, $result);
    $request = $result->first();
    static::assertSame($fundingCase->getId(), $request['funding_case_id']);
    static::assertSame('new', $request['status']);
    static::assertSame(100.0, $request['amount_requested']);
  }

  public function testApprove(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create([
      'status' => 'ongoing',
      'amount_approved' => 50.0,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseBundle->getFundingCaseType()->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE],
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $result = FundingAmountApprovedChangeRequest::approve(FALSE)
      ->setIds([$request->getId()])
      ->execute();

    static::assertCount(1, $result);
    static::assertSame('approved', $result->first()['status']);
    static::assertSame(120.0, $result->first()['amount_approved']);

    $updatedCase = FundingCase::get(FALSE)
      ->addWhere('id', '=', $fundingCase->getId())
      ->execute()->first();
    static::assertSame(120.0, $updatedCase['amount_approved']);
  }

  public function testApprovePartial(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create([
      'status' => 'ongoing',
      'amount_approved' => 50.0,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseBundle->getFundingCaseType()->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE],
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $result = FundingAmountApprovedChangeRequest::approvePartial(FALSE)
      ->setAmountApproved(80.0)
      ->setIds([$request->getId()])
      ->execute();

    static::assertCount(1, $result);
    static::assertSame('approved_partial', $result->first()['status']);
    static::assertSame(80.0, $result->first()['amount_approved']);

    $updatedCase = FundingCase::get(FALSE)
      ->addWhere('id', '=', $fundingCase->getId())
      ->execute()->first();
    static::assertSame(80.0, $updatedCase['amount_approved']);
  }

  public function testReject(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create([
      'status' => 'ongoing',
      'amount_approved' => 50.0,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $result = FundingAmountApprovedChangeRequest::reject(FALSE)
      ->setIds([$request->getId()])
      ->execute();

    static::assertCount(1, $result);
    static::assertSame('rejected', $result->first()['status']);

    $updatedCase = FundingCase::get(FALSE)
      ->addWhere('id', '=', $fundingCase->getId())
      ->execute()->first();
    static::assertSame(50.0, $updatedCase['amount_approved']);
  }

  public function testApproveNotAllowedWithoutReviewCalculativePermission(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create([
      'status' => 'ongoing',
      'amount_approved' => 50.0,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['view']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Not authorized to review this change request.');

    FundingAmountApprovedChangeRequest::approve(FALSE)
      ->setIds([$request->getId()])
      ->execute();
  }

  public function testApproveNotAllowedIfFundingCaseIsNotOngoing(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create([
      'status' => 'open',
      'amount_approved' => 50.0,
    ]);
    $fundingCase = $fundingCaseBundle->getFundingCase();
    PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Not authorized to review this change request.');

    FundingAmountApprovedChangeRequest::approve(FALSE)
      ->setIds([$request->getId()])
      ->execute();
  }

  public function testApproveNonExistentFundingCase(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['view', 'review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS = 0');
    FundingAmountApprovedChangeRequest::update(FALSE)
      ->addWhere('id', '=', $request->getId())
      ->setValues(['funding_case_id' => 999999])
      ->execute();
    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS = 1');

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Funding case with ID "999999" not found');

    FundingAmountApprovedChangeRequest::approve(FALSE)
      ->setIds([$request->getId()])
      ->execute();
  }

  public function testApprovePartialNonExistentFundingCase(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['view', 'review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS = 0');
    FundingAmountApprovedChangeRequest::update(FALSE)
      ->addWhere('id', '=', $request->getId())
      ->setValues(['funding_case_id' => 999999])
      ->execute();
    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS = 1');

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Funding case with ID "999999" not found');

    FundingAmountApprovedChangeRequest::approvePartial(FALSE)
      ->setIds([$request->getId()])
      ->setAmountApproved(80.0)
      ->execute();
  }

  public function testRejectNonExistentFundingCase(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['view', 'review_calculative']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS = 0');
    FundingAmountApprovedChangeRequest::update(FALSE)
      ->addWhere('id', '=', $request->getId())
      ->setValues(['funding_case_id' => 999999])
      ->execute();
    CRM_Core_DAO::executeQuery('SET FOREIGN_KEY_CHECKS = 1');

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Funding case with ID "999999" not found');

    FundingAmountApprovedChangeRequest::reject(FALSE)
      ->setIds([$request->getId()])
      ->execute();
  }

  public function testApproveWithoutPermission(): void {
    $this->expectException(UnauthorizedException::class);
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM]);

    FundingAmountApprovedChangeRequest::approve()
      ->setIds([1])
      ->execute();
  }

  public function testApprovePartialWithoutPermission(): void {
    $this->expectException(UnauthorizedException::class);
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM]);

    FundingAmountApprovedChangeRequest::approvePartial()
      ->setIds([1])
      ->setAmountApproved(50.0)
      ->execute();
  }

  public function testRejectWithoutPermission(): void {
    $this->expectException(UnauthorizedException::class);
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM]);

    FundingAmountApprovedChangeRequest::reject()
      ->setIds([1])
      ->execute();
  }

  public function testGetWithoutPermission(): void {
    $this->expectException(UnauthorizedException::class);
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM]);
    FundingAmountApprovedChangeRequest::get()->execute();
  }

  public function testUpdateNotPermitted(): void {
    $this->expectException(UnauthorizedException::class);
    FundingAmountApprovedChangeRequest::update()
      ->addValue('amount_approved', 100.0)
      ->addWhere('id', '=', 1)
      ->execute();
  }

  public function testDeleteNotPermitted(): void {
    $this->expectException(UnauthorizedException::class);
    FundingAmountApprovedChangeRequest::delete()
      ->addWhere('id', '=', 1)
      ->execute();
  }

  public function testFixtureWithDefaults(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();

    $entity = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id'],
      ['comment' => 'Test comment']
    );

    static::assertGreaterThan(0, $entity->getId());
    static::assertSame($fundingCase->getId(), $entity->getFundingCaseId());
    static::assertSame($contact['id'], $entity->getCreationContactId());
    static::assertSame('new', $entity->getStatus());
    static::assertSame(120.0, $entity->getAmountRequested());
    static::assertSame('Test comment', $entity->getComment());
    static::assertNull($entity->getDecisionDate());
    static::assertNull($entity->getDecisionContactId());
    static::assertNull($entity->getAmountApproved());
  }

}
