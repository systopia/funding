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
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\KeywordValidator;
use Opis\JsonSchema\Parsers\KeywordValidatorParser;
use Opis\JsonSchema\Parsers\SchemaParser;

/**
 * The keyword "$costItems" has to be used for arrays containing cost item data.
 *
 * @see \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\JsonSchemaCostItems
 */
final class CostItemsKeywordValidatorParser extends KeywordValidatorParser {

  public function __construct(string $keyword = '$costItems') {
    parent::__construct($keyword);
  }

  /**
   * @inheritDoc
   */
  public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?KeywordValidator {
    if (!$this->keywordExists($info)) {
      return NULL;
    }

    $dataType = $info->data()->type ?? NULL;
    if (is_array($dataType) && 2 === count($dataType)) {
      if ('null' === $dataType[0]) {
        $dataType = $dataType[1];
      }
      elseif ('null' === $dataType[1]) {
        $dataType = $dataType[0];
      }
    }

    if ('array' !== $dataType) {
      throw new ParseException(
        '$costItems may only be applied on properties with data type array'
      );
    }

    $costItemDataFactory = ArrayCostItemDataFactory::parse($info->data(), $parser);

    return new CostItemsKeywordValidator($costItemDataFactory);
  }

}
