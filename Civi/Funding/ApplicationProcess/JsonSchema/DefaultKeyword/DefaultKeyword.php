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

use Opis\JsonSchema\Helper;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\Errors\ValidationError;
use Systopia\JsonSchema\Expression\Variables\Variable;
use Systopia\JsonSchema\Keywords\SetValueTrait;

/**
 * @see \Civi\Funding\ApplicationProcess\JsonSchema\DefaultKeyword\DefaultKeywordParser
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
