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

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\RemoteTools\Api3\Api3;
use Civi\Funding\Fixtures\BankingAccountFixture;
use Civi\Funding\Fixtures\BankingAccountReferenceFixture;

/**
 * @covers \Civi\Funding\PayoutProcess\BankAccountManager
 *
 * @group headless
 */
final class BankAccountManagerTest extends AbstractFundingHeadlessTestCase {

  private BankAccountManager $bankAccountManager;

  protected function setUp(): void {
    parent::setUp();
    $this->bankAccountManager = new BankAccountManager(new Api3());
  }

  public function testGetBankAccountReferenceByContactId(): void {
    $contact = ContactFixture::addIndividual();
    static::assertNull($this->bankAccountManager->getBankAccountByContactId($contact['id']));

    $bankingAccount1 = BankingAccountFixture::addFixture([
      'contact_id' => $contact['id'],
      'data_parsed' => json_encode(['BIC' => 'BIC1234']),
    ]);
    BankingAccountReferenceFixture::addFixture($bankingAccount1['id'], ['reference' => 'DE07123412341234123412']);

    static::assertEquals(
      new BankAccount('BIC1234', 'DE07123412341234123412'),
      $this->bankAccountManager->getBankAccountByContactId($contact['id']),
    );
    static::assertNull($this->bankAccountManager->getBankAccountByContactId($contact['id'] + 1));

    // Account without BIC
    $bankingAccount2 = BankingAccountFixture::addFixture(['contact_id' => $contact['id']]);
    BankingAccountReferenceFixture::addFixture($bankingAccount2['id'], ['reference' => 'DE07123412341234123413']);
    static::assertEquals(
      new BankAccount('', 'DE07123412341234123413'),
      $this->bankAccountManager->getBankAccountByContactId($contact['id']),
    );
  }

}
