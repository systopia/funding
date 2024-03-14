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

namespace Civi\Funding\ApplicationProcess\JsonSchema\Validator;

use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemKeywordValidatorParser;
use Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemsKeywordValidatorParser;
use Civi\Funding\ApplicationProcess\JsonSchema\DefaultKeyword\DefaultKeywordParser;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemKeywordValidatorParser;
use Civi\Funding\ApplicationProcess\JsonSchema\ResourcesItem\ResourcesItemsKeywordValidatorParser;
use Systopia\JsonSchema\Parsers\SystopiaVocabulary;

/**
 * @codeCoverageIgnore
 */
final class ApplicationSchemaVocabulary extends SystopiaVocabulary {

  /**
   * @inheritDoc
   */
  public function __construct(array $keywords = [], array $keywordValidators = [], array $pragmas = []) {
    $keywords[] = new DefaultKeywordParser();
    parent::__construct($keywords, $keywordValidators, $pragmas);
    $this->keywordValidators[] = new CostItemKeywordValidatorParser();
    $this->keywordValidators[] = new CostItemsKeywordValidatorParser();
    $this->keywordValidators[] = new ResourcesItemKeywordValidatorParser();
    $this->keywordValidators[] = new ResourcesItemsKeywordValidatorParser();
  }

}
