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

namespace Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem;

use Civi\Funding\Util\Uuid;
use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Expression\ExpressionVariablesContainer;
use Systopia\JsonSchema\Expression\Variables\JsonPointerVariable;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Keywords\SetValueTrait;
use Webmozart\Assert\Assert;

/**
 * ResourcesItemData factory for array items.
 */
final class ArrayResourcesItemDataFactory {

  use SetValueTrait;

  /**
   * @phpstan-var non-empty-string
   */
  private string $type;

  private string $identifierProperty;

  private string $amountProperty;

  private ExpressionVariablesContainer $propertiesContainer;

  /**
   * @param \stdClass $arraySchema
   *   Object specifying a JSON array that contains "$resourcesItems" as keyword.
   *
   * @throws \Opis\JsonSchema\Exceptions\ParseException
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public static function parse(\stdClass $arraySchema, SchemaParser $parser): self {
  // phpcs:enable
    $resourcesItemsSchema = $arraySchema->{'$resourcesItems'} ?? NULL;
    if (!$resourcesItemsSchema instanceof \stdClass) {
      throw new ParseException('Invalid $resourcesItems keyword configuration');
    }

    if (!self::propertyIsNonEmptyString($resourcesItemsSchema, 'type')) {
      throw new ParseException('type must be a non empty string');
    }

    $items = $arraySchema->items ?? NULL;
    if (!$items instanceof \stdClass || 'object' !== ($items->type ?? NULL)) {
      throw new ParseException('Array items must be of type "object" for use with $resourcesItems');
    }

    if (!self::propertyIsNonEmptyString($resourcesItemsSchema, 'identifierProperty')) {
      throw new ParseException('identifierProperty is required');
    }

    $identifierProperty = $resourcesItemsSchema->identifierProperty;
    $identifierDataType = self::getPropertyDataType($items->properties, $identifierProperty);
    if ('string' !== $identifierDataType) {
      throw new ParseException(
        'The identifier property must exist and its data type must be "string" (and optionally "null")'
      );
    }

    if (!self::propertyIsNonEmptyString($resourcesItemsSchema, 'amountProperty')) {
      throw new ParseException('amountProperty is required');
    }

    $amountProperty = $resourcesItemsSchema->amountProperty;
    $amountDataType = self::getPropertyDataType($items->properties, $amountProperty);
    if ('number' !== $amountDataType && 'integer' !== $amountDataType) {
      throw new ParseException(
        'The amount property must exist and its data type must be "number" or "integer" (and optionally "null")'
      );
    }

    $properties = [];
    foreach ($items->properties as $name => $property) {
      $properties[$name] = (object) ['$data' => '0/' . $name];
    }
    $propertiesContainer = ExpressionVariablesContainer::parse((object) $properties, $parser);

    return new self($resourcesItemsSchema->type, $identifierProperty, $amountProperty, $propertiesContainer);
  }

  private static function getPropertyDataType(\stdClass $properties, string $propertyName): ?string {
    $dataType = $properties->{$propertyName}->type ?? NULL;
    if (is_array($dataType) && 2 === count($dataType)) {
      if ('null' === $dataType[0]) {
        $dataType = $dataType[1];
      }
      elseif ('null' === $dataType[1]) {
        $dataType = $dataType[0];
      }
    }

    return is_string($dataType) ? $dataType : NULL;
  }

  private static function propertyIsNonEmptyString(\stdClass $data, string $propertyName): bool {
    return property_exists($data, $propertyName)
      && is_string($data->{$propertyName})
      && '' !== $data->{$propertyName};
  }

  /**
   * @phpstan-param non-empty-string $type
   */
  private function __construct(
    string $type,
    string $identifierProperty,
    string $amountProperty,
    ExpressionVariablesContainer $propertiesContainer,
  ) {
    $this->type = $type;
    $this->identifierProperty = $identifierProperty;
    $this->amountProperty = $amountProperty;
    $this->propertiesContainer = $propertiesContainer;
  }

  /**
   * The amount of the returned resources item data is 0, if the amount in the
   * context data is NULL.
   *
   * @throws \InvalidArgumentException
   * @throws \Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException
   */
  public function createResourcesItemData(ValidationContext $context): ?ResourcesItemData {
    $dataType = $context->currentDataType();
    if ('null' === $dataType) {
      return NULL;
    }

    if ('object' !== $dataType) {
      throw new \InvalidArgumentException(
        sprintf('Expected data type object got "%s"', $dataType)
      );
    }

    $amount = $this->getPropertyValue($context, $this->amountProperty);
    if (NULL === $amount) {
      return NULL;
    }

    if (!is_float($amount) && !is_int($amount)) {
      throw new \InvalidArgumentException('Amount could not be resolved to a number');
    }

    $identifier = $this->getOrGenerateIdentifier($context);
    $properties = $this->propertiesContainer->getValues($context, Variable::FLAG_FAIL_ON_VIOLATION);
    /** @phpstan-var non-empty-string $dataPointer */
    $dataPointer = JsonPointer::pathToString($context->currentDataPath());

    return new ResourcesItemData([
      'type' => $this->type,
      'identifier' => $identifier,
      'amount' => (float) $amount,
      'properties' => $properties,
      'dataPointer' => $dataPointer,
      'dataType' => $dataType,
    ]);
  }

  /**
   * @phpstan-return non-empty-string
   *
   * @throws \Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException
   * @throws \InvalidArgumentException
   */
  private function getOrGenerateIdentifier(ValidationContext $context): string {
    $identifier = $this->getPropertyValue($context, $this->identifierProperty);
    if (NULL === $identifier || '' === $identifier) {
      $identifierProperty = $this->identifierProperty;
      $identifier = Uuid::generateRandom();
      // @phpstan-ignore-next-line
      $this->setValue($context, function (\stdClass $data) use ($identifierProperty, $identifier) {
        $data->{$identifierProperty} = $identifier;

        return $data;
      });
    }

    if (!is_string($identifier)) {
      throw new \InvalidArgumentException('Identifier could not be resolved to a string');
    }
    /** @phpstan-var non-empty-string $identifier */

    if (1 !== preg_match('/^[a-zA-Z0-9.\-_]+$/', $identifier)) {
      throw new \InvalidArgumentException('Identifier may only contain letters, numbers, ".", "-", and "_"');
    }

    return $identifier;
  }

  /**
   * @return mixed
   *
   * @throws \Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException
   */
  private function getPropertyValue(ValidationContext $context, string $propertyName) {
    $pointer = JsonPointer::parse('0/' . $propertyName);
    Assert::notNull($pointer);

    return (new JsonPointerVariable($pointer))->getValue($context, Variable::FLAG_FAIL_ON_VIOLATION);
  }

}
