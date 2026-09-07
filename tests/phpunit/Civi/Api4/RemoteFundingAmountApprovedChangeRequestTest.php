<?php

declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\AttachmentFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use CRM_Funding_ExtensionUtil as E;

/**
 * @covers \Civi\Api4\RemoteFundingAmountApprovedChangeRequest
 * @covers \Civi\Funding\Api4\Action\Remote\AmountApprovedChangeRequest\CreateAction
 *
 * @group headless
 */
final class RemoteFundingAmountApprovedChangeRequestTest extends AbstractRemoteFundingHeadlessTestCase {

  public function testCreate(): void {
    $fundingCase = $this->createFundingCase();
    AttachmentFixture::addFixture(
      'civicrm_funding_case',
      $fundingCase->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT],
    );

    $contact = ContactFixture::addIndividual();
    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $fundingCase->getFundingProgramId(),
      ['application_program_perm', 'CAN_create_amount_approved_change_request', 'amount_approved_change_request_create']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), [
      'application_case_perm',
      'CAN_create_amount_approved_change_request',
      'amount_approved_change_request_create',
    ]);

    $request = RemoteFundingAmountApprovedChangeRequest::create()
      ->setRemoteContactId((string) $contact['id'])
      ->setFundingCaseId($fundingCase->getId())
      ->setAmountRequested(200.0)
      ->setComment('Test comment');
    $request->execute();

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
