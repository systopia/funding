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
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @implements TokenResolverInterface<AbstractEntity>
 */
final class TokenResolver implements TokenResolverInterface {

  private Api4Interface $api4;

  private MoneyFactory $moneyFactory;

  private PropertyAccessorInterface $propertyAccessor;

  public function __construct(
    Api4Interface $api4,
    MoneyFactory $moneyFactory,
    PropertyAccessorInterface $propertyAccessor
  ) {
    $this->api4 = $api4;
    $this->moneyFactory = $moneyFactory;
    $this->propertyAccessor = $propertyAccessor;
  }

  /**
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  public function resolveToken(string $entityName, AbstractEntity $entity, string $tokenName): ResolvedToken {
    $value = $this->getValue($entityName, $entity, $tokenName);

    return ValueConverter::toResolvedToken($this->convertMoneyValue($value, $entity, $entityName, $tokenName));
  }

  /**
   * @return mixed
   *
   * @throws \CRM_Core_Exception
   *
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  private function getValue(string $entityName, AbstractEntity $entity, string $tokenName) {
    /** @var string $fieldName */
    [$fieldName, $path] = explode('::', $tokenName, 2) + [NULL, NULL];
    $entityValue = $this->getEntityValue($entityName, $entity, $fieldName);

    if (NULL === $path) {
      return $entityValue;
    }

    if (!is_array($entityValue)) {
      return NULL;
    }

    if ('' === $path) {
      $value = $entityValue;
    }
    else {
      $value = \CRM_Utils_Array::pathGet($entityValue, explode('.', $path));
    }

    return is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $value;
  }

  /**
   * @return mixed
   *
   * @throws \CRM_Core_Exception
   *
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  private function getEntityValue(string $entityName, AbstractEntity $entity, string $fieldName) {
    if ($this->propertyAccessor->isReadable($entity, $fieldName)) {
      return $this->propertyAccessor->getValue($entity, $fieldName);
    }

    if ($entity->has($fieldName)) {
      return $entity->get($fieldName);
    }

    // Fallback to APIv4. Should not be necessary, but for custom fields and suffixed fields.
    $values = $this->api4->execute($entityName, 'get', [
      'select' => [$fieldName],
      'where' => [['id', '=', $entity->getId()]],
      'checkPermissions' => FALSE,
    ])->first();

    if (is_array($values) && array_key_exists($fieldName, $values)) {
      return $values[$fieldName];
    }

    throw new \RuntimeException(\sprintf('Unknown token "%s" for "%s"', $fieldName, $entityName));
  }

  /**
   * @param mixed $value
   *
   * @return mixed
   *
   * @throws \CRM_Core_Exception
   *
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  private function convertMoneyValue($value, AbstractEntity $entity, string $entityName, string $tokenName) {
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
