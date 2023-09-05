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

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\Api4;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Traits\FundingCaseTestFixturesTrait;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\AttachmentFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Util\RequestTestUtil;
use CRM_Funding_ExtensionUtil as E;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingCase
 * @covers \Civi\Funding\Api4\Action\FundingCase\GetAction
 * @covers \Civi\Funding\EventSubscriber\FundingCase\FundingCasePermissionsGetSubscriber
 */
final class FundingCaseTest extends AbstractFundingHeadlessTestCase {

  use FundingCaseTestFixturesTrait;

  public function testApprove(): void {
    $this->addInternalFixtures();

    RequestTestUtil::mockInternalRequest($this->associatedContactId);

    $e = NULL;
    try {
      FundingCase::approve()
        ->setId($this->permittedFundingCaseId)
        ->setTitle('title')
        ->setAmount(123.45)
        ->execute();
    }
    catch (UnauthorizedException $e) {
      static::assertSame('Approving this funding case is not allowed.', $e->getMessage());
    }
    static::assertNotNull($e);

    FundingCaseContactRelationFixture::addContact(
      $this->associatedContactId,
      $this->permittedFundingCaseId,
      ['review_calculative'],
    );

    AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $this->fundingCaseTypeId,
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE],
    );

    $result = FundingCase::approve()
      ->setId($this->permittedFundingCaseId)
      ->setTitle('title')
      ->setAmount(123.45)
      ->execute();

    static::assertSame('title', $result['title']);
    static::assertSame(123.45, $result['amount_approved']);
    static::assertSame('ongoing', $result['status']);
  }

  public function testRecreateTransferContract(): void {
    $this->addInternalFixtures();

    RequestTestUtil::mockInternalRequest($this->associatedContactId);

    FundingCase::update(FALSE)
      ->addWhere('id', '=', $this->permittedFundingCaseId)
      ->addValue('status', 'ongoing')
      ->addValue('amount_approved', 1.23)
      ->execute();

    $e = NULL;
    try {
      FundingCase::recreateTransferContract()
        ->setId($this->permittedFundingCaseId)
        ->execute();
    }
    catch (UnauthorizedException $e) {
      static::assertSame('Permission to recreate transfer contract is missing.', $e->getMessage());
    }
    static::assertNotNull($e);

    FundingCaseContactRelationFixture::addContact(
      $this->associatedContactId,
      $this->permittedFundingCaseId,
      ['review_calculative'],
    );

    AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $this->fundingCaseTypeId,
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE],
    );

    $transferContractAttachment = AttachmentFixture::addFixture(
      'civicrm_funding_case',
      $this->permittedFundingCaseId,
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT],
    );

    $result = FundingCase::recreateTransferContract()
      ->setId($this->permittedFundingCaseId)
      ->execute();
    static::assertSame($this->permittedFundingCaseId, $result['id']);

    // Previous transfer contract should have been removed.
    static::assertFileDoesNotExist($transferContractAttachment->getPath());
  }

  public function testPermissionsInternal(): void {
    $this->addInternalFixtures();

    // Admin gets view permissions for all cases.
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);
    $adminResult = FundingCase::get()->execute();
    static::assertSame(2, $adminResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $adminResult->first()['id']);
    static::assertSame(['view'], $adminResult->first()['permissions']);
    static::assertTrue($adminResult->first()['PERM_view']);
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ACCESS_FUNDING]);

    // Contact is directly associated
    RequestTestUtil::mockInternalRequest($this->associatedContactId);
    $permittedAssociatedResult = FundingCase::get()->execute();
    static::assertSame(1, $permittedAssociatedResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedAssociatedResult->first()['id']);
    static::assertSame(['review_baz'], $permittedAssociatedResult->first()['permissions']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_review_baz']);
    static::assertNull($permittedAssociatedResult->first()['transfer_contract_uri']);

    // Contact has an a-b-relationship with an associated contact
    RequestTestUtil::mockInternalRequest($this->relatedABContactId);
    $permittedABResult = FundingCase::get()->execute();
    static::assertSame(1, $permittedABResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedABResult->first()['id']);
    static::assertSame(['review_e'], $permittedABResult->first()['permissions']);
    static::assertTrue($permittedABResult->first()['PERM_review_e']);
    static::assertNull($permittedABResult->first()['transfer_contract_uri']);

    // Contact has an b-a-relationship with an associated contact
    RequestTestUtil::mockInternalRequest($this->relatedBAContactId);
    $permittedBAResult = FundingCase::get()
      ->execute();
    static::assertSame(1, $permittedBAResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedBAResult->first()['id']);
    static::assertSame(['review_e'], $permittedBAResult->first()['permissions']);
    static::assertTrue($permittedBAResult->first()['PERM_review_e']);
    static::assertNull($permittedBAResult->first()['transfer_contract_uri']);

    // Contact has a not permitted relationship with an associated contact
    RequestTestUtil::mockInternalRequest($this->notPermittedContactId);
    $notPermittedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact is directly associated, but has no permissions set
    RequestTestUtil::mockInternalRequest($this->associatedContactIdNoPermissions);
    $permittedAssociatedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $permittedAssociatedResult->rowCount);

    // Contact is directly associated, but has application and review permissions
    RequestTestUtil::mockInternalRequest($this->associatedContactIdApplicationAndReview);
    $permittedAssociatedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $permittedAssociatedResult->rowCount);
  }

  public function testPermissionsRemote(): void {
    $this->addRemoteFixtures();

    // Contact is directly associated
    RequestTestUtil::mockRemoteRequest((string) $this->associatedContactId);
    $permittedAssociatedResult = FundingCase::get()
      ->execute();
    static::assertSame(1, $permittedAssociatedResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedAssociatedResult->first()['id']);
    static::assertSame(['application_foo', 'application_bar'], $permittedAssociatedResult->first()['permissions']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_application_foo']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_application_bar']);
    static::assertArrayNotHasKey('PERM_review_baz', $permittedAssociatedResult->first());
    static::assertNull($permittedAssociatedResult->first()['transfer_contract_uri']);

    // Contact has an a-b-relationship with an associated contact
    RequestTestUtil::mockRemoteRequest((string) $this->relatedABContactId);
    $permittedABResult = FundingCase::get()
      ->execute();
    static::assertSame(1, $permittedABResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedABResult->first()['id']);
    static::assertSame(['application_c', 'application_d'], $permittedABResult->first()['permissions']);
    static::assertTrue($permittedABResult->first()['PERM_application_c']);
    static::assertTrue($permittedABResult->first()['PERM_application_d']);
    static::assertArrayNotHasKey('PERM_review_e', $permittedABResult->first());
    static::assertNull($permittedABResult->first()['transfer_contract_uri']);

    // Contact has an b-a-relationship with an associated contact
    RequestTestUtil::mockRemoteRequest((string) $this->relatedBAContactId);
    $permittedBAResult = FundingCase::get()
      ->execute();
    static::assertSame(1, $permittedBAResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedBAResult->first()['id']);
    static::assertSame(['application_c', 'application_d'], $permittedBAResult->first()['permissions']);
    static::assertTrue($permittedBAResult->first()['PERM_application_c']);
    static::assertTrue($permittedBAResult->first()['PERM_application_d']);
    static::assertArrayNotHasKey('PERM_review_e', $permittedBAResult->first());
    static::assertNull($permittedBAResult->first()['transfer_contract_uri']);

    // Contact has a not permitted relationship with an associated contact
    RequestTestUtil::mockRemoteRequest((string) $this->notPermittedContactId);
    $notPermittedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact is directly associated, but has no permissions set
    RequestTestUtil::mockRemoteRequest((string) $this->associatedContactIdNoPermissions);
    $permittedAssociatedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $permittedAssociatedResult->rowCount);
  }

}
