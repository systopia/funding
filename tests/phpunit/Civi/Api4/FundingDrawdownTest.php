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
use Civi\Funding\Util\SessionTestUtil;

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

    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $result = FundingDrawdown::get()->addSelect('id', 'currency')->execute();
    static::assertCount(1, $result);
    static::assertSame(
      [
        'id' => $drawdown->getId(),
        'currency' => FundingProgramFixture::DEFAULT_CURRENCY,
      ],
      $result->first(),
    );

    SessionTestUtil::mockRemoteRequestSession((string) $contactNotPermitted['id']);
    static::assertCount(0, FundingDrawdown::get()
      ->addSelect('id')->execute());
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
