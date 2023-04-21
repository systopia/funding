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

namespace Civi\Funding\PayoutProcess;

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\DrawdownFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;
use Civi\Funding\Util\SessionTestUtil;
use Civi\RemoteTools\Api4\Api4;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Civi\Funding\PayoutProcess\PayoutProcessManager
 *
 * @group headless
 */
final class PayoutProcessManagerHeadlessTest extends AbstractFundingHeadlessTestCase {

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  private PayoutProcessManager $payoutProcessManager;

  protected function setUp(): void {
    parent::setUp();
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->payoutProcessManager = new PayoutProcessManager(
      new Api4(),
      $this->eventDispatcherMock,
    );
  }

  public function testGetAmountAvailable(): void {
    $payoutProcess = $this->createPayoutProcess(['amount_total' => 100.0]);
    $requesterContactId = ContactFixture::addIndividual()['id'];

    FundingCaseContactRelationFixture::addContact(
      $requesterContactId,
      $payoutProcess->getFundingCaseId(),
      ['drawdown_create'],
    );

    SessionTestUtil::mockRemoteRequestSession((string) $requesterContactId);
    static::assertSame(100.0, $this->payoutProcessManager->getAmountAvailable($payoutProcess));

    DrawdownFixture::addFixture($payoutProcess->getId(), $requesterContactId, ['amount' => 12.34]);
    static::assertSame(100.0 - 12.34, $this->payoutProcessManager->getAmountAvailable($payoutProcess));

    DrawdownFixture::addFixture($payoutProcess->getId(), $requesterContactId, ['amount' => 10]);
    static::assertSame(100.0 - 22.34, $this->payoutProcessManager->getAmountAvailable($payoutProcess));
  }

  public function testGetAmountAccepted(): void {
    $payoutProcess = $this->createPayoutProcess(['amount_total' => 100.0]);
    $requesterContactId = ContactFixture::addIndividual()['id'];

    FundingCaseContactRelationFixture::addContact(
      $requesterContactId,
      $payoutProcess->getFundingCaseId(),
      ['drawdown_create'],
    );

    SessionTestUtil::mockRemoteRequestSession((string) $requesterContactId);
    static::assertSame(0.0, $this->payoutProcessManager->getAmountAccepted($payoutProcess));

    DrawdownFixture::addFixture($payoutProcess->getId(), $requesterContactId, [
      'amount' => 12.34,
      'status' => 'accepted',
    ]);
    DrawdownFixture::addFixture($payoutProcess->getId(), $requesterContactId, [
      'amount' => 11.0,
      'status' => 'new',
    ]);
    static::assertSame(12.34, $this->payoutProcessManager->getAmountAccepted($payoutProcess));

    DrawdownFixture::addFixture($payoutProcess->getId(), $requesterContactId, [
      'amount' => 10,
      'status' => 'accepted',
    ]);
    static::assertSame(22.34, $this->payoutProcessManager->getAmountAccepted($payoutProcess));
  }

  public function testGetAmountRequested(): void {
    $payoutProcess = $this->createPayoutProcess(['amount_total' => 100.0]);
    $requesterContactId = ContactFixture::addIndividual()['id'];

    FundingCaseContactRelationFixture::addContact(
      $requesterContactId,
      $payoutProcess->getFundingCaseId(),
      ['drawdown_create'],
    );

    SessionTestUtil::mockRemoteRequestSession((string) $requesterContactId);
    static::assertSame(0.0, $this->payoutProcessManager->getAmountRequested($payoutProcess));

    DrawdownFixture::addFixture($payoutProcess->getId(), $requesterContactId, ['amount' => 12.34]);
    static::assertSame(12.34, $this->payoutProcessManager->getAmountRequested($payoutProcess));

    DrawdownFixture::addFixture($payoutProcess->getId(), $requesterContactId, ['amount' => 10]);
    static::assertSame(22.34, $this->payoutProcessManager->getAmountRequested($payoutProcess));
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
