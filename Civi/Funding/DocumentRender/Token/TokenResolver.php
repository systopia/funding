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
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @implements TokenResolverInterface<AbstractEntity>
 */
final class TokenResolver implements TokenResolverInterface {

  private Api4Interface $api4;

  private PropertyAccessorInterface $propertyAccessor;

  public function __construct(Api4Interface $api4, PropertyAccessorInterface $propertyAccessor) {
    $this->api4 = $api4;
    $this->propertyAccessor = $propertyAccessor;
  }

  /**
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  public function resolveToken(string $entityName, AbstractEntity $entity, string $tokenName): ResolvedToken {
    return ValueConverter::toResolvedToken($this->getValue($entityName, $entity, $tokenName));
  }

  /**
   * @return mixed
   *
   * @throws \CRM_Core_Exception
   *
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  private function getValue(string $entityName, AbstractEntity $entity, string $tokenName) {
    if ($this->propertyAccessor->isReadable($entity, $tokenName)) {
      return $this->propertyAccessor->getValue($entity, $tokenName);
    }

    if ($entity->has($tokenName)) {
      return $entity->get($tokenName);
    }

    // Fallback to APIv4. Should not be necessary, though.
    $values = $this->api4->execute($entityName, 'get', [
      'select' => [$tokenName],
      'where' => [['id', '=', $entity->getId()]],
      'checkPermissions' => FALSE,
    ])->first();

    if (is_array($values) && array_key_exists($tokenName, $values)) {
      return $values[$tokenName];
    }

    throw new \RuntimeException(\sprintf('Unknown token "%s" for "%s"', $tokenName, $entityName));
  }

}
