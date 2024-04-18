<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\JsonSchema\DefaultKeyword;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;

/**
 * This class ensures that a property is initialized so its actual default value
 * will be parsed. (Keywords are only applied to property that actually exist.)
 *
 * @see \Civi\Funding\ApplicationProcess\JsonSchema\DefaultKeyword\DefaultKeywordParser
 * @see \Civi\Funding\ApplicationProcess\JsonSchema\DefaultKeyword\DefaultKeyword
 */
final class DefaultInitKeyword implements Keyword {

  /**
   * @phpstan-var array<int|string>
   */
  private array $propertiesWithDefault;

  /**
   * @param array<int|string> $propertiesWithDefault
   */
  public function __construct(array $propertiesWithDefault) {
    $this->propertiesWithDefault = $propertiesWithDefault;
  }

  /**
   * @inheritDoc
   */
  public function validate(ValidationContext $context, Schema $schema): ?ValidationError {
    $data = $context->currentData();

    if ($data instanceof \stdClass) {
      foreach ($this->propertiesWithDefault as $propertyName) {
        $data->{$propertyName} ??= NULL;
      }
    }

    return NULL;
  }

}
