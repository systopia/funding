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

namespace Civi\Funding\JsonSchema\Util;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Webmozart\Assert\Assert;
use CRM_Funding_ExtensionUtil as E;

final class JsonSchemaValueUtil {

  /**
   * @param scalar|null $value
   */
  public static function valueToString($value, JsonSchema $itemPropertySchema): string {
    /** @phpstan-var list<JsonSchema> $oneOf */
    $oneOf = $itemPropertySchema->getKeywordValueOrDefault('oneOf', []);
    foreach ($oneOf as $oneOfEntry) {
      if ($oneOfEntry->hasKeyword('const') && $oneOfEntry->getKeywordValue('const') === $value) {
        if ($oneOfEntry->hasKeyword('title')) {
          $title = $oneOfEntry->getKeywordValue('title');
          Assert::string($title);

          return $title;
        }

        break;
      }
    }

    if (is_bool($value)) {
      return $value ? E::ts('Yes') : E::ts('No');
    }

    return (string) $value;
  }

}
