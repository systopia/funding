<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\DocumentRender\Token;

use Civi\Funding\Entity\AbstractEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * @template T of AbstractEntity = AbstractEntity
 */
class TokenValueLoader {

  /**
   * @param array<mixed> $array
   */
  private static function isAllScalarOrNull(array $array): bool {
    foreach ($array as $value) {
      if (!is_scalar($value) && NULL !== $value) {
        return FALSE;
      }
    }

    return TRUE;
  }

  public function __construct(
    private readonly Api4Interface $api4,
    private readonly PropertyAccessorInterface $propertyAccessor
  ) {}

  /**
   * @phpstan-param T $entity
   *
   * @throws \CRM_Core_Exception
   */
  public function getValue(string $entityName, AbstractEntity $entity, string $tokenName): mixed {
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

    return (is_array($value) && (!array_is_list($value) || !self::isAllScalarOrNull($value)))
      ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
      : $value;
  }

  /**
   * @phpstan-param T $entity
   *
   * @throws \CRM_Core_Exception
   */
  private function getEntityValue(string $entityName, AbstractEntity $entity, string $fieldName): mixed {
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

}
