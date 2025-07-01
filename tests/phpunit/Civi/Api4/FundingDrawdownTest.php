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

namespace Civi\Api4;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\DrawdownFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\FundingDrawdown
 * @covers \Civi\Funding\Api4\Action\FundingDrawdown\GetAction
 *
 * @group headless
 */
final class FundingDrawdownTest extends AbstractFundingHeadlessTestCase {

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $contactNotPermitted = ContactFixture::addIndividual();
    $payoutProcess = $this->createPayoutProcess();
    $drawdown = DrawdownFixture::addFixture($payoutProcess->getId(), $contact['id']);

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $payoutProcess->getFundingCaseId(),
      ['drawdown_permission'],
    );

    RequestTestUtil::mockRemoteRequest((string) $contact['id']);
    $result = FundingDrawdown::get()
      ->addSelect('id', 'amount_accepted', 'amount_paid_out', 'amount_new')
      ->execute();
    static::assertCount(1, $result);
    static::assertEquals(
      [
        'id' => $drawdown->getId(),
        'amount_accepted' => 0.0,
        'amount_paid_out' => 0.0,
        'amount_new' => $drawdown->getAmount(),
      ],
      $result->first(),
    );

    RequestTestUtil::mockRemoteRequest((string) $contactNotPermitted['id']);
    static::assertCount(0, FundingDrawdown::get()
      ->addSelect('id')->execute());
  }

  public function testGetAccepted(): void {
    $contact = ContactFixture::addIndividual();
    $payoutProcess = $this->createPayoutProcess();
    $drawdown = DrawdownFixture::addFixture(
      $payoutProcess->getId(),
      $contact['id'],
      ['amount' => 1.23, 'status' => 'accepted']
    );

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $payoutProcess->getFundingCaseId(),
      ['some_permission'],
    );

    RequestTestUtil::mockInternalRequest($contact['id']);
    $result = FundingDrawdown::get()
      ->addSelect('id', 'amount_accepted', 'amount_paid_out', 'amount_new')
      ->execute();
    static::assertCount(1, $result);
    static::assertEquals(
      [
        'id' => $drawdown->getId(),
        'amount_accepted' => 1.23,
        'amount_paid_out' => 1.23,
        'amount_new' => 0.0,
      ],
      $result->first(),
    );
  }

  public function testGetAcceptedPaybackClaim(): void {
    $contact = ContactFixture::addIndividual();
    $payoutProcess = $this->createPayoutProcess();
    $drawdown = DrawdownFixture::addFixture(
      $payoutProcess->getId(),
      $contact['id'],
      ['amount' => -1.23, 'status' => 'accepted']
    );

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $payoutProcess->getFundingCaseId(),
      ['some_permission'],
    );

    RequestTestUtil::mockInternalRequest($contact['id']);
    $result = FundingDrawdown::get()
      ->addSelect('id', 'currency', 'amount_accepted', 'amount_paid_out', 'amount_new')
      ->execute();
    static::assertCount(1, $result);
    static::assertEquals(
      [
        'id' => $drawdown->getId(),
        'currency' => FundingProgramFixture::DEFAULT_CURRENCY,
        'amount_accepted' => -1.23,
        'amount_paid_out' => 0.0,
        'amount_new' => 0.0,
      ],
      $result->first(),
    );
  }

  private function createPayoutProcess(): PayoutProcessEntity {
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

    return PayoutProcessFixture::addFixture($fundingCase->getId());
  }

}
