<?php

declare(strict_types = 1);

namespace Civi\Api4;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingAmountApprovedChangeRequestFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;

/**
 * @covers \Civi\Api4\RemoteFundingAmountApprovedChangeRequest
 * @covers \Civi\Funding\Api4\Action\Remote\AmountApprovedChangeRequest\CreateAction
 *
 * @group headless
 */
final class RemoteFundingAmountApprovedChangeRequestTest extends AbstractRemoteFundingHeadlessTestCase {

  public function testCreate(): void {
    $fundingCase = $this->createFundingCase();

    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['amountApprovedChangeRequest_create']
    );

    FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $result = RemoteFundingAmountApprovedChangeRequest::create()
      ->setRemoteContactId((string) $contact['id'])
      ->setFundingCaseId($fundingCase->getId())
      ->setAmountRequested(200.0)
      ->setComment('Test comment')
      ->execute();

    $record = $result->single();
    static::assertSame($fundingCase->getId(), $record['funding_case_id']);
    static::assertSame(200.0, $record['amount_requested']);
    static::assertSame('Test comment', $record['comment']);
  }

  public function testCreateNotAllowedWithoutAmountApprovedChangeRequestCreatePermission(): void {
    $fundingCase = $this->createFundingCase();

    $contact = ContactFixture::addIndividual();
    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getFundingProgramId(),
      []
    );
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['application_case_perm']
    );

    FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Not authorized to create this change request.');

    RemoteFundingAmountApprovedChangeRequest::create()
      ->setRemoteContactId((string) $contact['id'])
      ->setFundingCaseId($fundingCase->getId())
      ->setAmountRequested(200.0)
      ->setComment('Test comment')
      ->execute();
  }

  public function testGet(): void {
    $fundingCase = $this->createFundingCase();

    $contact = ContactFixture::addIndividual();
    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getFundingProgramId(),
      []
    );
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['amountApprovedChangeRequest_create']
    );

    $request = FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id'],
      ['amount_requested' => 123.45]
    );

    $result = RemoteFundingAmountApprovedChangeRequest::get()
      ->setRemoteContactId((string) $contact['id'])
      ->addSelect('id', 'funding_case_id', 'amount_requested')
      ->execute();

    static::assertCount(1, $result);
    static::assertSame(
      [
        'id' => $request->getId(),
        'funding_case_id' => $fundingCase->getId(),
        'amount_requested' => 123.45,
      ],
      $result->first()
    );
  }

  public function testGetAllowedWithoutAmountApprovedChangeRequestCreatePermission(): void {
    $fundingCase = $this->createFundingCase();

    $contact = ContactFixture::addIndividual();
    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getFundingProgramId(),
      []
    );
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      ['application_case_perm']
    );

    FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $result = RemoteFundingAmountApprovedChangeRequest::get()
      ->setRemoteContactId((string) $contact['id'])
      ->addSelect('id')
      ->execute();

    static::assertCount(1, $result);
  }

  public function testGetNotAllowedWithoutFundingCaseAccess(): void {
    $fundingCase = $this->createFundingCase();

    $contact = ContactFixture::addIndividual();
    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getFundingProgramId(),
      []
    );
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getId(),
      []
    );

    FundingAmountApprovedChangeRequestFixture::addFixture(
      $fundingCase->getId(),
      $contact['id']
    );

    $result = RemoteFundingAmountApprovedChangeRequest::get()
      ->setRemoteContactId((string) $contact['id'])
      ->addSelect('id')
      ->execute();

    static::assertCount(0, $result);
  }

  private function createFundingCase(): FundingCaseEntity {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();

    return FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
      ['status' => 'ongoing', 'amount_approved' => 12.34]
    );
  }

}
