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

namespace Civi\Funding\DocumentRender\Token;

use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Util\MoneyFactory;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @implements TokenResolverInterface<AbstractEntity>
 */
final class TokenResolver implements TokenResolverInterface {

  public function __construct(
    private readonly Api4Interface $api4,
    private readonly MoneyFactory $moneyFactory,
    private readonly TokenValueLoader $tokenValueLoader
  ) {}

  public function resolveToken(string $entityName, AbstractEntity $entity, string $tokenName): ResolvedToken {
    $value = $this->tokenValueLoader->getValue($entityName, $entity, $tokenName);

    return ValueConverter::toResolvedToken($this->convertMoneyValue($value, $entity, $entityName, $tokenName));
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function convertMoneyValue(
    mixed $value,
    AbstractEntity $entity,
    string $entityName,
    string $tokenName
  ): mixed {
    if (!is_float($value)) {
      return $value;
    }

    $result = $this->api4->execute($entityName, 'getFields', [
      'select' => ['data_type'],
      'where' => [['name', '=', $tokenName]],
      'checkPermissions' => FALSE,
    ]);

    if ('Money' === ($result->first()['data_type'] ?? NULL)) {
      /** @var string|null $currency */
      $currency = $entity->get('currency');

      return $this->moneyFactory->createMoney($value, $currency);
    }

    return $value;
  }

}
