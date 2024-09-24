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

use Civi\Api4\Traits\FundingCaseTypeFixturesTrait;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\AttachmentFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Util\RequestTestUtil;
use CRM_Funding_ExtensionUtil as E;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingCaseType
 * @covers \Civi\Funding\Api4\Action\FundingCaseType\GetAction
 * @covers \Civi\Funding\Api4\Action\FundingCaseType\GetByFundingProgramIdAction
 * @covers \Civi\Funding\Api4\Action\FundingCaseType\SaveAction
 * @covers \Civi\Funding\Api4\Action\FundingCaseType\UpdateAction
 */
final class FundingCaseTypeTest extends AbstractFundingHeadlessTestCase {

  use FundingCaseTypeFixturesTrait;

  public function testGetTransferContractTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $result = FundingCaseType::get()
      ->addSelect('transfer_contract_template_file_id')
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame([NULL], $result->column('transfer_contract_template_file_id'));

    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE],
    );

    $result = FundingCaseType::get()
      ->addSelect('transfer_contract_template_file_id')
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame([$attachment->getId()], $result->column('transfer_contract_template_file_id'));
  }

  public function testGetPaybackClaimTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $result = FundingCaseType::get()
      ->addSelect('payback_claim_template_file_id')
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame([NULL], $result->column('payback_claim_template_file_id'));

    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::PAYBACK_CLAIM_TEMPLATE],
    );

    $result = FundingCaseType::get()
      ->addSelect('payback_claim_template_file_id')
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame([$attachment->getId()], $result->column('payback_claim_template_file_id'));
  }

  public function testGetPaymentInstructionTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $result = FundingCaseType::get()
      ->addSelect('payment_instruction_template_file_id')
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame([NULL], $result->column('payment_instruction_template_file_id'));

    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE],
    );

    $result = FundingCaseType::get()
      ->addSelect('payment_instruction_template_file_id')
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame([$attachment->getId()], $result->column('payment_instruction_template_file_id'));
  }

  public function testGetByFundingProgramId(): void {
    $this->addFixtures();
    RequestTestUtil::mockRemoteRequest((string) $this->permittedContactId);
    static::assertCount(1, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramId)
      ->execute());

    static::assertCount(0, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramIdWithoutFundingCaseType)
      ->execute());

    RequestTestUtil::mockRemoteRequest((string) $this->notPermittedContactId);
    static::assertCount(0, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramId)
      ->execute());
  }

  public function testUpdatePaybackClaimTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    $result = FundingCaseType::update()
      ->addValue('payback_claim_template_file_id', $attachment->getId())
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame($attachment->getId(), $result->single()['payback_claim_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::PAYBACK_CLAIM_TEMPLATE, $fileTypeName);

    $attachment2 = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    // Previous file shall be deleted.
    $result = FundingCaseType::update()
      ->addValue('payback_claim_template_file_id', $attachment2->getId())
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame($attachment2->getId(), $result->single()['payback_claim_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment2->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::PAYBACK_CLAIM_TEMPLATE, $fileTypeName);

    static::assertCount(
      0,
      File::get(FALSE)->addWhere('id', '=', $attachment->getId())->execute()
    );
  }

  public function testUpdatePaymentInstructionTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    $result = FundingCaseType::update()
      ->addValue('payment_instruction_template_file_id', $attachment->getId())
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame($attachment->getId(), $result->single()['payment_instruction_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE, $fileTypeName);

    $attachment2 = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    // Previous file shall be deleted.
    $result = FundingCaseType::update()
      ->addValue('payment_instruction_template_file_id', $attachment2->getId())
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame($attachment2->getId(), $result->single()['payment_instruction_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment2->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE, $fileTypeName);

    static::assertCount(
      0,
      File::get(FALSE)->addWhere('id', '=', $attachment->getId())->execute()
    );
  }

  public function testUpdateTransferContractTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    $result = FundingCaseType::update()
      ->addValue('transfer_contract_template_file_id', $attachment->getId())
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame($attachment->getId(), $result->single()['transfer_contract_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::TRANSFER_CONTRACT_TEMPLATE, $fileTypeName);

    $attachment2 = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    // Previous file shall be deleted.
    $result = FundingCaseType::update()
      ->addValue('transfer_contract_template_file_id', $attachment2->getId())
      ->addWhere('id', '=', $fundingCaseType->getId())
      ->execute();
    static::assertSame($attachment2->getId(), $result->single()['transfer_contract_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment2->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::TRANSFER_CONTRACT_TEMPLATE, $fileTypeName);

    static::assertCount(
      0,
      File::get(FALSE)->addWhere('id', '=', $attachment->getId())->execute()
    );
  }

  public function testSavePaybackClaimTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    $result = FundingCaseType::save()
      ->addRecord([
        'id' => $fundingCaseType->getId(),
        'payback_claim_template_file_id' => $attachment->getId(),
      ])
      ->execute();
    static::assertSame($attachment->getId(), $result->single()['payback_claim_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::PAYBACK_CLAIM_TEMPLATE, $fileTypeName);

    $attachment2 = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    // Previous file shall be deleted.
    $result = FundingCaseType::save()
      ->addRecord([
        'id' => $fundingCaseType->getId(),
        'payback_claim_template_file_id' => $attachment2->getId(),
      ])
      ->execute();
    static::assertSame($attachment2->getId(), $result->single()['payback_claim_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment2->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::PAYBACK_CLAIM_TEMPLATE, $fileTypeName);

    static::assertCount(
      0,
      File::get(FALSE)->addWhere('id', '=', $attachment->getId())->execute()
    );
  }

  public function testSavePaymentInstructionTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    $result = FundingCaseType::save()
      ->addRecord([
        'id' => $fundingCaseType->getId(),
        'payment_instruction_template_file_id' => $attachment->getId(),
      ])
      ->execute();
    static::assertSame($attachment->getId(), $result->single()['payment_instruction_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE, $fileTypeName);

    $attachment2 = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    // Previous file shall be deleted.
    $result = FundingCaseType::save()
      ->addRecord([
        'id' => $fundingCaseType->getId(),
        'payment_instruction_template_file_id' => $attachment2->getId(),
      ])
      ->execute();
    static::assertSame($attachment2->getId(), $result->single()['payment_instruction_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment2->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE, $fileTypeName);

    static::assertCount(
      0,
      File::get(FALSE)->addWhere('id', '=', $attachment->getId())->execute()
    );
  }

  public function testSaveTransferContractTemplateFileId(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $attachment = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    $result = FundingCaseType::save()
      ->addRecord([
        'id' => $fundingCaseType->getId(),
        'transfer_contract_template_file_id' => $attachment->getId(),
      ])
      ->execute();
    static::assertSame($attachment->getId(), $result->single()['transfer_contract_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::TRANSFER_CONTRACT_TEMPLATE, $fileTypeName);

    $attachment2 = AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx')
    );

    // Previous file shall be deleted.
    $result = FundingCaseType::save()
      ->addRecord([
        'id' => $fundingCaseType->getId(),
        'transfer_contract_template_file_id' => $attachment2->getId(),
      ])
      ->execute();
    static::assertSame($attachment2->getId(), $result->single()['transfer_contract_template_file_id']);

    $fileTypeName = File::get(FALSE)
      ->addSelect('file_type_id:name')
      ->addWhere('id', '=', $attachment2->getId())
      ->execute()
      ->single()['file_type_id:name'];
    static::assertSame(FileTypeNames::TRANSFER_CONTRACT_TEMPLATE, $fileTypeName);

    static::assertCount(
      0,
      File::get(FALSE)->addWhere('id', '=', $attachment->getId())->execute()
    );
  }

}
