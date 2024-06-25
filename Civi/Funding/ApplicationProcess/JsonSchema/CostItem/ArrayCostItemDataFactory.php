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

namespace Civi\Funding\ApplicationProcess\JsonSchema\CostItem;

use Civi\Funding\ApplicationProcess\JsonSchema\FinancePlanItem\ArgumentAssert;
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
 * CostItemData factory for array items.
 *
 * @phpstan-type clearingT array{itemLabel: string}
 */
final class ArrayCostItemDataFactory {

  use SetValueTrait;

  /**
   * @phpstan-var non-empty-string
   */
  private string $type;

  private string $identifierProperty;

  private string $amountProperty;

  private ExpressionVariablesContainer $propertiesContainer;

  /**
   * @phpstan-var clearingT|null
   */
  private ?array $clearing;

  /**
   * @param \stdClass $arraySchema
   *   Object specifying a JSON array that contains "$costItems" as keyword.
   *
   * @throws \Opis\JsonSchema\Exceptions\ParseException
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public static function parse(\stdClass $arraySchema, SchemaParser $parser): self {
  // phpcs:enable
    $costItemsSchema = $arraySchema->{'$costItems'} ?? NULL;
    if (!$costItemsSchema instanceof \stdClass) {
      throw new ParseException('Invalid $costItems keyword configuration');
    }

    if (!self::propertyIsNonEmptyString($costItemsSchema, 'type')) {
      throw new ParseException('type must be a non empty string');
    }

    $items = $arraySchema->items ?? NULL;
    if (!$items instanceof \stdClass || 'object' !== ($items->type ?? NULL)) {
      throw new ParseException('Array items must be of type "object" for use with $costItems');
    }

    if (!self::propertyIsNonEmptyString($costItemsSchema, 'identifierProperty')) {
      throw new ParseException('identifierProperty is required');
    }

    $identifierProperty = $costItemsSchema->identifierProperty;
    $identifierDataType = self::getPropertyDataType($items->properties, $identifierProperty);
    if ('string' !== $identifierDataType) {
      throw new ParseException(
        'The identifier property must exist and its data type must be "string" (and optionally "null")'
      );
    }

    if (!self::propertyIsNonEmptyString($costItemsSchema, 'amountProperty')) {
      throw new ParseException('amountProperty is required');
    }

    $amountProperty = $costItemsSchema->amountProperty;
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

    $clearing = self::parseClearing($costItemsSchema);

    return new self($costItemsSchema->type, $identifierProperty, $amountProperty, $propertiesContainer, $clearing);
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

  /**
   * @phpstan-return clearingT|null
   *
   * @throws \Opis\JsonSchema\Exceptions\ParseException
   */
  private static function parseClearing(\stdClass $data): ?array {
    if (!property_exists($data, 'clearing')) {
      return NULL;
    }

    if (!self::propertyIsNonEmptyString($data->clearing, 'itemLabel')) {
      throw new ParseException('If clearing is enabled, an item label has to be set');
    }

    return [
      'itemLabel' => $data->clearing->itemLabel,
    ];
  }

  private static function propertyIsNonEmptyString(\stdClass $data, string $propertyName): bool {
    return property_exists($data, $propertyName)
      && is_string($data->{$propertyName})
      && '' !== $data->{$propertyName};
  }

  /**
   * @phpstan-param non-empty-string $type
   * @phpstan-param clearingT|null $clearing
   *   NULL if no clearing is required.
   */
  private function __construct(
    string $type,
    string $identifierProperty,
    string $amountProperty,
    ExpressionVariablesContainer $propertiesContainer,
    ?array $clearing
  ) {
    $this->type = $type;
    $this->identifierProperty = $identifierProperty;
    $this->amountProperty = $amountProperty;
    $this->propertiesContainer = $propertiesContainer;
    $this->clearing = $clearing;
  }

  /**
   * The amount of the returned cost item data is 0, if the amount in the
   * context data is NULL.
   *
   * @throws \InvalidArgumentException
   * @throws \Systopia\JsonSchema\Exceptions\ReferencedDataHasViolationException
   */
  public function createCostItemData(ValidationContext $context): ?CostItemData {
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

    return new CostItemData([
      'type' => $this->type,
      'identifier' => $identifier,
      'amount' => (float) $amount,
      'properties' => $properties,
      'clearing' => $this->clearing,
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

    ArgumentAssert::assertIdentifier($identifier);

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
