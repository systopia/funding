<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\DrawdownFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;

/**
 * @covers \Civi\Api4\RemoteFundingPayoutProcess
 *
 * @group headless
 */
final class RemoteFundingPayoutProcessTest extends AbstractRemoteFundingHeadlessTestCase {

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $contactNotPermitted = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $payoutProcess = PayoutProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['application_permission']);

    $result = RemoteFundingPayoutProcess::get()
      ->setRemoteContactId((string) $contact['id'])
      ->addSelect('id', 'currency', 'amount_accepted', 'amount_paid_out', 'amount_new')
      ->execute();
    static::assertCount(1, $result);
    static::assertEquals([
      'id' => $payoutProcess->getId(),
      'currency' => FundingProgramFixture::DEFAULT_CURRENCY,
      'amount_accepted' => 0.0,
      'amount_paid_out' => 0.0,
      'amount_new' => 0.0,
    ], $result->first());

    DrawdownFixture::addFixture($payoutProcess->getId(), $contactNotPermitted['id'], ['amount' => 0.1]);
    DrawdownFixture::addFixture($payoutProcess->getId(), $contactNotPermitted['id'], [
      'amount' => 0.4,
      'status' => 'accepted',
    ]);
    DrawdownFixture::addFixture($payoutProcess->getId(), $contactNotPermitted['id'], [
      'amount' => -0.2,
      'status' => 'accepted',
    ]);
    $result = RemoteFundingPayoutProcess::get()
      ->setRemoteContactId((string) $contact['id'])
      ->addSelect('id', 'currency', 'amount_accepted', 'amount_paid_out', 'amount_new')
      ->execute();
    static::assertCount(1, $result);
    static::assertEquals([
      'id' => $payoutProcess->getId(),
      'currency' => FundingProgramFixture::DEFAULT_CURRENCY,
      'amount_accepted' => 0.2,
      'amount_paid_out' => 0.4,
      'amount_new' => 0.1,
    ], $result->first());

    $this->clearCache();
    static::assertCount(0, RemoteFundingPayoutProcess::get()
      ->setRemoteContactId((string) $contactNotPermitted['id'])
      ->addSelect('id')->execute());
  }

  public function testGetFields(): void {
    static::assertCount(10, RemoteFundingPayoutProcess::getFields()->execute());
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
    );
  }

}
