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

use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingCaseInfo
 * @covers \Civi\Funding\Api4\Action\FundingCaseInfo\GetAction
 * @covers \Civi\Funding\Api4\Action\FundingCaseInfo\GetFieldsAction
 */
final class FundingCaseInfoTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testGet(): void {
    $recipientContact = ContactFixture::addOrganization();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
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

    $expected = [
      'funding_case_id' => $fundingCase->getId(),
      'funding_case_permissions' => ['case_perm'],
      'funding_case_status' => $fundingCase->getStatus(),
      'funding_case_creation_date' => $fundingCase->getCreationDate()->format('Y-m-d H:i:s'),
      'funding_case_modification_date' => $fundingCase->getModificationDate()->format('Y-m-d H:i:s'),
      'funding_case_type_id' => $fundingCaseType->getId(),
      'funding_program_id' => $fundingProgram->getId(),
      'funding_program_currency' => $fundingProgram->getCurrency(),
      'funding_program_title' => $fundingProgram->getTitle(),
      'application_process_id' => $applicationProcess->getId(),
      'application_process_title' => $applicationProcess->getTitle(),
      'application_process_short_description' => $applicationProcess->getShortDescription(),
      'application_process_status' => $applicationProcess->getStatus(),
      'application_process_is_review_calculative' => $applicationProcess->getIsReviewCalculative(),
      'application_process_is_review_content' => $applicationProcess->getIsReviewContent(),
      'application_process_amount_requested' => $applicationProcess->getAmountRequested(),
      'application_process_amount_granted' => $applicationProcess->getAmountGranted(),
      'application_process_granted_budget' => $applicationProcess->getGrantedBudget(),
      'application_process_creation_date' => $applicationProcess->getCreationDate()->format('Y-m-d H:i:s'),
      'application_process_modification_date' => $applicationProcess->getModificationDate()->format('Y-m-d H:i:s'),
      'application_process_start_date' => '2022-09-20 20:20:20',
      'application_process_end_date' => NULL,
    ];
    static::assertEquals($expected, $result->first());

    $fundingCase2 = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id']
    );
    $applicationProcess2 = ApplicationProcessFixture::addFixture(
      $fundingCase2->getId(),
      ['title' => 'Application 2', 'end_date' => '2022-09-21 21:21:21']
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
  }

  public function testGetFields(): void {
    $action = FundingCaseInfo::getFields()->setLoadOptions(TRUE);
    $result = $action->execute();

    static::assertCount(22, $result);
    /** @phpstan-var array<string, mixed> $field */
    foreach ($result as $field) {
      static::assertNotEmpty($field['name']);
      static::assertNotEmpty($field['data_type']);
      static::assertTrue($field['readonly']);
      if ('funding_case_status' === $field['name'] || 'application_process_status' === $field['name']) {
        static::assertIsArray($field['options']);
        static::assertNotEmpty($field['options']);
      }
      else {
        static::assertFalse($field['options']);
      }
    }
  }

}
