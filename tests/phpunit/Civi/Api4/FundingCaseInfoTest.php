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

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\AttachmentFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Util\TestUtil;
use Civi\RemoteTools\Api4\RemoteApiConstants;
use CRM_Funding_ExtensionUtil as E;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingCaseInfo
 * @covers \Civi\Funding\Api4\Action\FundingCaseInfo\GetAction
 * @covers \Civi\Funding\Api4\Action\FundingCaseInfo\GetFieldsAction
 */
final class FundingCaseInfoTest extends AbstractFundingHeadlessTestCase {

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
      ['amount_approved' => 12.34],
    );
    AttachmentFixture::addFixture(
      'civicrm_funding_case',
      $fundingCase->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT],
    );
    $applicationProcess = ApplicationProcessFixture::addFixture(
      $fundingCase->getId(),
      ['start_date' => '2022-09-20 20:20:20']
    );

    $contact = ContactFixture::addIndividual();
    FundingProgramContactRelationFixture::addContact($contact['id'], $fundingProgram->getId(), ['program_perm']);
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['case_perm']);

    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    $action = FundingCaseInfo::get();
    $result = $action->execute();
    static::assertCount(1, $result);

    /** @var array<string, mixed> $values */
    $values = $result->first();
    $expected = [
      'funding_case_id' => $fundingCase->getId(),
      'funding_case_identifier' => $fundingCase->getIdentifier(),
      'funding_case_permissions' => ['case_perm'],
      'funding_case_status' => $fundingCase->getStatus(),
      'funding_case_creation_date' => $fundingCase->getCreationDate()->format('Y-m-d H:i:s'),
      'funding_case_modification_date' => $fundingCase->getModificationDate()->format('Y-m-d H:i:s'),
      'funding_case_amount_approved' => 12.34,
      'funding_case_transfer_contract_uri'
      => 'http://localhost/civicrm/funding/transfer-contract/download?fundingCaseId=' . $fundingCase->getId(),
      'funding_case_type_id' => $fundingCaseType->getId(),
      'funding_case_type_is_combined_application' => $fundingCaseType->getIsCombinedApplication(),
      'funding_program_id' => $fundingProgram->getId(),
      'funding_program_currency' => $fundingProgram->getCurrency(),
      'funding_program_title' => $fundingProgram->getTitle(),
      'application_process_id' => $applicationProcess->getId(),
      'application_process_identifier' => $applicationProcess->getIdentifier(),
      'application_process_title' => $applicationProcess->getTitle(),
      'application_process_short_description' => $applicationProcess->getShortDescription(),
      'application_process_status' => $applicationProcess->getStatus(),
      'application_process_is_review_calculative' => $applicationProcess->getIsReviewCalculative(),
      'application_process_is_review_content' => $applicationProcess->getIsReviewContent(),
      'application_process_amount_requested' => $applicationProcess->getAmountRequested(),
      'application_process_creation_date' => $applicationProcess->getCreationDate()->format('Y-m-d H:i:s'),
      'application_process_modification_date' => $applicationProcess->getModificationDate()->format('Y-m-d H:i:s'),
      'application_process_start_date' => '2022-09-20 20:20:20',
      'application_process_end_date' => NULL,
      'application_process_is_eligible' => $applicationProcess->getIsEligible(),
      'funding_case_PERM_case_perm' => TRUE,
    ];
    static::assertEquals($expected,
      // Not given, but possible permissions are part of the flattened permissions
      TestUtil::filterFlattenedPermissions($values, 'funding_case_' . RemoteApiConstants::PERMISSION_FIELD_PREFIX)
    );
    static::assertGreaterThan(\count($expected), \count($values));

    $fundingCase2 = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );
    $applicationProcess2 = ApplicationProcessFixture::addFixture(
      $fundingCase2->getId(),
      ['title' => 'Application 2', 'identifier' => 'app2', 'end_date' => '2022-09-21 21:21:21']
    );
    $result = $action->execute();
    static::assertCount(1, $result);

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase2->getId(), ['test']);
    $result = $action->execute();
    static::assertCount(2, $result);

    $action->addWhere('funding_case_id', '=', $fundingCase2->getId());
    $result = $action->execute();
    static::assertCount(1, $result);
    static::assertSame($fundingCase2->getId(), $result->first()['funding_case_id']);
    static::assertSame($applicationProcess2->getId(), $result->first()['application_process_id']);

    $action->setWhere([])->addWhere('application_process_id', '=', $applicationProcess->getId());
    $result = $action->execute();
    static::assertCount(1, $result);
    static::assertSame($fundingCase->getId(), $result->first()['funding_case_id']);
    static::assertSame($applicationProcess->getId(), $result->first()['application_process_id']);
  }

  public function testGetFields(): void {
    $action = FundingCaseInfo::getFields()->setLoadOptions(TRUE);
    $result = $action->execute();

    $permissionsCount = 0;
    /** @phpstan-var array<string, mixed> $field */
    foreach ($result as $field) {
      static::assertIsString($field['name']);
      static::assertNotEmpty($field['name']);
      $message = sprintf('Failed for field %s', $field['name']);
      static::assertSame('Extra', $field['type'], $message);
      static::assertNotEmpty($field['data_type'], $message);
      static::assertTrue($field['readonly'], $message);
      if (in_array(
        $field['name'],
        ['funding_case_status', 'funding_case_permissions', 'application_process_status'],
        TRUE
      )) {
        static::assertIsArray($field['options'], $message);
        static::assertNotEmpty($field['options'], $message);

        if ('funding_case_permissions' === $field['name']) {
          $permissionsCount = count($field['options']);
          static::assertGreaterThan(0, $permissionsCount);
        }
      }
      else {
        static::assertFalse($field['options'], $message);
      }
    }

    static::assertCount(26 + $permissionsCount, $result);
  }

}
