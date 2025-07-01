<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingProgram;

use Civi\Api4\FundingProgram;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ApplicationCostItemFixture;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ClearingCostItemFixture;
use Civi\Funding\Fixtures\ClearingProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\DrawdownFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * @covers \Civi\Funding\Api4\Action\FundingProgram\GetAction
 *
 * @group headless
 */
final class GetActionTest extends AbstractFundingHeadlessTestCase {

  use ArraySubsetAsserts;

  public function testAmountApproved(): void {
    $fundingProgram1 = FundingProgramFixture::addFixture();
    $fundingProgram2 = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();

    FundingCaseFixture::addFixture(
      $fundingProgram2->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => 10],
    );

    static::assertSame(['amount_approved' => 0.0], FundingProgram::get()
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addSelect('amount_approved')
      ->addWhere('id', '=', $fundingProgram1->getId())
      ->execute()
      ->single());

    FundingCaseFixture::addFixture(
      $fundingProgram1->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => 123.45],
    );
    FundingCaseFixture::addFixture(
      $fundingProgram1->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => 100],
    );

    static::assertSame(['amount_approved' => 223.45], FundingProgram::get()
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addSelect('amount_approved')
      ->addWhere('id', '=', $fundingProgram1->getId())
      ->execute()
      ->single());
  }

  public function testAmountAvailable(): void {
    $fundingProgram1 = FundingProgramFixture::addFixture(['budget' => 100.1]);
    $fundingProgram2 = FundingProgramFixture::addFixture(['budget' => 200]);
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();

    FundingCaseFixture::addFixture(
      $fundingProgram2->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => 10],
    );

    static::assertSame(['amount_available' => 100.1], FundingProgram::get()
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addSelect('amount_available')
      ->addWhere('id', '=', $fundingProgram1->getId())
      ->execute()
      ->single());

    FundingCaseFixture::addFixture(
      $fundingProgram1->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => .1],
    );
    FundingCaseFixture::addFixture(
      $fundingProgram1->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => 20],
    );
    FundingCaseFixture::addFixture(
      $fundingProgram1->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => 0],
    );

    static::assertSame(['amount_available' => 80.0], FundingProgram::get()
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addSelect('amount_available')
      ->addWhere('id', '=', $fundingProgram1->getId())
      ->execute()
      ->single());
  }

  public function testAmountDrawdownsAcceptedAndAmountPaidOut(): void {
    $fundingProgram1 = FundingProgramFixture::addFixture();
    $fundingProgram2 = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();

    $fundingCase1 = FundingCaseFixture::addFixture(
      $fundingProgram1->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => 100],
    );
    $payoutProcess1 = PayoutProcessFixture::addFixture($fundingCase1->getId());

    $fundingCase2 = FundingCaseFixture::addFixture(
      $fundingProgram2->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id'],
      ['amount_approved' => 200],
    );
    $payoutProcess2 = PayoutProcessFixture::addFixture($fundingCase2->getId());

    DrawdownFixture::addFixture($payoutProcess2->getId(), $contact['id'], [
      'status' => 'accepted',
      'amount' => 1,
    ]);

    static::assertSame([
      'amount_drawdowns_accepted' => 0.0,
      'amount_paid_out' => 0.0,
    ], FundingProgram::get()
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addSelect('amount_drawdowns_accepted', 'amount_paid_out')
      ->addWhere('id', '=', $fundingProgram1->getId())
      ->execute()
      ->single());

    DrawdownFixture::addFixture($payoutProcess1->getId(), $contact['id'], [
      'status' => 'accepted',
      'amount' => 1.2,
    ]);
    DrawdownFixture::addFixture($payoutProcess1->getId(), $contact['id'], [
      'status' => 'accepted',
      'amount' => 2.3,
    ]);
    DrawdownFixture::addFixture($payoutProcess1->getId(), $contact['id'], [
      'status' => 'accepted',
      'amount' => -1.1,
    ]);
    DrawdownFixture::addFixture($payoutProcess1->getId(), $contact['id'], [
      'status' => 'new',
      'amount' => 10,
    ]);

    static::assertSame([
      'amount_drawdowns_accepted' => 2.4,
      'amount_paid_out' => 3.5,
    ], FundingProgram::get()
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addSelect('amount_drawdowns_accepted', 'amount_paid_out')
      ->addWhere('id', '=', $fundingProgram1->getId())
      ->execute()
      ->single());

    $applicationProcess1 = ApplicationProcessFixture::addFixture($fundingCase1->getId());
    $clearingProcess1 = ClearingProcessFixture::addFixture($applicationProcess1->getId());
    $applicationCostItem1_1 = ApplicationCostItemFixture::addFixture($applicationProcess1->getId());
    $applicationCostItem1_2 = ApplicationCostItemFixture::addFixture($applicationProcess1->getId());
    ClearingCostItemFixture::addFixture($clearingProcess1->getId(), $applicationCostItem1_1->getId(), [
      'amount' => 6.7,
      'amount_admitted' => 8.9,
    ]);
    ClearingCostItemFixture::addFixture($clearingProcess1->getId(), $applicationCostItem1_2->getId(), [
      'amount' => 7.7,
      'amount_admitted' => NULL,
    ]);

    $applicationProcess2 = ApplicationProcessFixture::addFixture($fundingCase2->getId());
    $clearingProcess2 = ClearingProcessFixture::addFixture($applicationProcess2->getId());
    $applicationCostItem2 = ApplicationCostItemFixture::addFixture($applicationProcess2->getId());
    ClearingCostItemFixture::addFixture($clearingProcess2->getId(), $applicationCostItem2->getId(), [
      'amount' => 7.8,
      'amount_admitted' => 1.2,
    ]);

    static::assertArraySubset([
      'amount_cleared' => 6.7 + 7.7,
      'amount_admitted' => 8.9,
    ], FundingProgram::get()
      ->setAllowEmptyRecordPermissions(TRUE)
      ->addSelect('amount_cleared', 'amount_admitted')
      ->addWhere('id', '=', $fundingProgram1->getId())
      ->execute()
      ->single()
    );
  }

}
