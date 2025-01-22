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

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;
use Civi\Funding\Validation\Exception\EntityValidationFailedException;

/**
 * @covers \Civi\Api4\RemoteFundingDrawdown
 * @covers \Civi\Funding\Api4\Action\Remote\Drawdown\CreateAction
 *
 * @group headless
 */
final class RemoteFundingDrawdownTest extends AbstractRemoteFundingHeadlessTestCase {

  protected function setUp(): void {
    parent::setUp();
  }

  public function testCreate(): void {
    $payoutProcess = $this->createPayoutProcess(['amount_total' => 100.0]);
    $contact = ContactFixture::addIndividual();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $payoutProcess->getFundingCaseId(),
      ['drawdown_create'],
    );

    $result = RemoteFundingDrawdown::create()
      ->setRemoteContactId((string) $contact['id'])
      ->setPayoutProcessId($payoutProcess->getId())
      ->setAmount(1.23)
      ->execute();

    $record = $result->single();
    static::assertSame($payoutProcess->getId(), $record['payout_process_id']);
    static::assertSame(1.23, $record['amount']);

    static::assertCount(1, RemoteFundingDrawdown::get()
      ->setRemoteContactId((string) $contact['id'])
      ->execute()
    );
  }

  public function testCreatePermissionMissing(): void {
    $payoutProcess = $this->createPayoutProcess(['amount_total' => 100.0]);
    $contact = ContactFixture::addIndividual();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $payoutProcess->getFundingCaseId(),
      ['application_modify'],
    );

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to create drawdown is missing.');

    RemoteFundingDrawdown::create()
      ->setRemoteContactId((string) $contact['id'])
      ->setPayoutProcessId($payoutProcess->getId())
      ->setAmount(1.23)
      ->execute();
  }

  public function testCreateAvailableAmountExceeded(): void {
    $payoutProcess = $this->createPayoutProcess(['amount_total' => 10.01]);
    $contact = ContactFixture::addIndividual();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $payoutProcess->getFundingCaseId(),
      ['drawdown_create'],
    );

    $this->expectException(EntityValidationFailedException::class);
    $this->expectExceptionMessage('Validation failed: Requested amount is greater than available amount.');

    RemoteFundingDrawdown::create()
      ->setRemoteContactId((string) $contact['id'])
      ->setPayoutProcessId($payoutProcess->getId())
      ->setAmount(10.1)
      ->execute();
  }

  public function testGetFields(): void {
    static::assertCount(12, RemoteFundingDrawdown::getFields()->execute());
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

  /**
   * @phpstan-param array<string, mixed> $values
   */
  private function createPayoutProcess(array $values = []): PayoutProcessEntity {
    $fundingCase = $this->createFundingCase();

    return PayoutProcessFixture::addFixture($fundingCase->getId(), $values);
  }

}
