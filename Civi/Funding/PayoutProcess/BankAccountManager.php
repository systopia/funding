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

use Civi\RemoteTools\Api3\Api3Interface;

/**
 * This class provides data stored through the extension CiviBanking.
 */
class BankAccountManager {

  private Api3Interface $api3;

  public function __construct(Api3Interface $api3) {
    $this->api3 = $api3;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function getBankAccountByContactId(int $contactId): ?BankAccount {
    $bankingAccount = $this->getLastBankingAccountByContactId($contactId);
    if (NULL === $bankingAccount || !is_string($bankingAccount['data_parsed']['BIC'] ?? '')) {
      return NULL;
    }

    $bankingAccountReference = $this->getBankingAccountReference($bankingAccount['id']);
    if (NULL === $bankingAccountReference) {
      return NULL;
    }

    return new BankAccount(
      $bankingAccount['data_parsed']['BIC'] ?? '',
      $bankingAccountReference,
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getBankingAccountReference(int $bankingAccountId): ?string {
    $result = $this->api3->execute('BankingAccountReference', 'get', [
      'return' => ['reference'],
      'ba_id' => $bankingAccountId,
      'sequential' => 1,
    ]);

    // @phpstan-ignore-next-line
    return $result['values'][0]['reference'] ?? NULL;
  }

  /**
   * @param int $contactId
   *
   * @phpstan-return array{id: int, data_parsed: array<string, mixed>}|null
   *
   * @throws \CRM_Core_Exception
   */
  private function getLastBankingAccountByContactId(int $contactId): ?array {
    $result = $this->api3->execute('BankingAccount', 'get', [
      'return' => ['id', 'data_parsed'],
      'contact_id' => $contactId,
      'options' => ['sort' => 'id DESC', 'limit' => 1],
      'sequential' => 1,
    ]);

    // @phpstan-ignore-next-line
    if (!isset($result['values'][0])) {
      return NULL;
    }

    /** @phpstan-var array{id: string, data_parsed: string} $values */
    // @phpstan-ignore-next-line
    $values = $result['values'][0];

    // @phpstan-ignore-next-line
    return [
      'id' => (int) $values['id'],
      'data_parsed' => json_decode($values['data_parsed'], TRUE, 5, \JSON_THROW_ON_ERROR),
    ];
  }

}
