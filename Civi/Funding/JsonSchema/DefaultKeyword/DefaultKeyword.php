<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\JsonSchema\DefaultKeyword;

use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Keywords\SetValueTrait;

/**
 * @see \Civi\Funding\JsonSchema\DefaultKeyword\DefaultKeywordParser
 */
final class DefaultKeyword implements Keyword {

  use SetValueTrait;

  private Variable $default;

  public function __construct(Variable $default) {
    $this->default = $default;
  }

  /**
   * @inheritDoc
   */
  public function validate(ValidationContext $context, Schema $schema): ?ValidationError {
    if (NULL === $context->currentData()) {
      $this->setValue($context, fn() => Helper::cloneValue($this->default->getValue($context)));
    }

    return NULL;
  }

}
