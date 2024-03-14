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

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\SchemaParser;
use Systopia\JsonSchema\Expression\Variables\Variable;

/**
 * Similar to the 'default' keyword, but allows to use '$data' as well as
 * '$calculate'. The value is set if the property exists, but is null, too.
 */
final class DefaultKeywordParser extends KeywordParser {

  /**
   * @inheritDoc
   */
  public function __construct() {
    parent::__construct('$default');
  }

  /**
   * @inheritDoc
   */
  public function type(): string {
    return self::TYPE_APPEND;
  }

  /**
   * @inheritDoc
   */
  public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword {
    $schema = $info->data();

    if ($parser->option('allowDefaults') !== TRUE) {
      return NULL;
    }

    $defaults = [];
    if (($schema->properties ?? NULL) instanceof \stdClass) {
      // @phpstan-ignore-next-line
      foreach ($schema->properties as $name => $value) {
        if ($value instanceof \stdClass && property_exists($value, $this->keyword)) {
          $defaults[$name] = Variable::create($value->{$this->keyword}, $parser);
        }
      }
    }

    return [] === $defaults ? NULL : new DefaultKeyword($defaults);
  }

}
