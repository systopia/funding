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

use Civi\RemoteTools\JsonSchema\JsonSchema;

/**
 * For the "$costItem" keyword.
 *
 * @see \Civi\Funding\ApplicationProcess\JsonSchema\CostItem\CostItemKeywordValidator
 *
 * @codeCoverageIgnore
 */
final class JsonSchemaCostItem extends JsonSchema {

  /**
   * @phpstan-param array{
   *   type: non-empty-string,
   *   identifier: non-empty-string,
   * } $config
   */
  public function __construct(array $config) {
    if (1 !== preg_match('/^[a-zA-Z0-9._\-]+$/', $config['identifier'])) {
      throw new \InvalidArgumentException(
        'identifier must not be empty and only consist of letters, numbers, ".", "_", and "-"'
      );
    }

    parent::__construct($config);
  }

}
