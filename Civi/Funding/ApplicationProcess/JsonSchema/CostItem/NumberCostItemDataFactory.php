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

use Opis\JsonSchema\Exceptions\ParseException;
use Opis\JsonSchema\JsonPointer;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\ValidationContext;

/**
 * CostItemData factory for numbers/integers.
 */
final class NumberCostItemDataFactory {

  /**
   * @phpstan-var non-empty-string
   */
  private string $type;

  /**
   * @phpstan-var non-empty-string
   */
  private string $identifier;

  /**
   * @param \stdClass $costItemSchema
   *   The cost item keyword data for a property of type number or integer.
   *
   * @throws \Opis\JsonSchema\Exceptions\ParseException
   */
  public static function parse(\stdClass $costItemSchema, SchemaParser $parser): self {
    if (!self::propertyIsNonEmptyString($costItemSchema, 'identifier')) {
      throw new ParseException('identifier is required');
    }

    if (0 === preg_match('/^[a-zA-Z0-9.\-_]+$/', $costItemSchema->identifier)) {
      throw new ParseException('identifier may only contain letters, numbers, ".", "-", and "_"');
    }

    if (!self::propertyIsNonEmptyString($costItemSchema, 'type')) {
      throw new ParseException('type must be a non empty string');
    }

    return new self(
      $costItemSchema->type,
      $costItemSchema->identifier,
    );
  }

  private static function propertyIsNonEmptyString(\stdClass $data, string $propertyName): bool {
    return property_exists($data, $propertyName)
      && is_string($data->{$propertyName})
      && '' !== $data->{$propertyName};
  }

  /**
   * @phpstan-param non-empty-string $type
   * @phpstan-param non-empty-string $identifier
   */
  private function __construct(
    string $type,
    string $identifier,
  ) {
    $this->type = $type;
    $this->identifier = $identifier;
  }

  /**
   * @throws \InvalidArgumentException
   */
  public function createCostItemData(ValidationContext $context): ?CostItemData {
    $dataType = $context->currentDataType();
    if ('null' === $dataType) {
      return NULL;
    }

    if ('number' !== $dataType && 'integer' !== $dataType) {
      throw new \InvalidArgumentException(
        sprintf('Expected data type object got "%s"', $dataType)
      );
    }

    $amount = $context->currentData();
    if (!is_float($amount) && !is_int($amount)) {
      throw new \InvalidArgumentException('Data is not a number');
    }

    /** @phpstan-var non-empty-string $dataPointer */
    $dataPointer = JsonPointer::pathToString($context->currentDataPath());

    return new CostItemData([
      'type' => $this->type,
      'identifier' => $this->identifier,
      'amount' => (float) $amount,
      'properties' => [],
      'dataPointer' => $dataPointer,
      'dataType' => $dataType,
    ]);
  }

}
