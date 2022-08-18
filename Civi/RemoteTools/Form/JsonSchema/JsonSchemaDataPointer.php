<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\RemoteTools\Form\JsonSchema;

/**
 * Non-standard element that points to another element.
 */
class JsonSchemaDataPointer extends JsonSchema {

  /**
   * @param string $path JSON pointer
   * @param scalar|null|JsonSchema $fallback
   *   Fallback is used if the pointed element does not exist or has no value.
   */
  public function __construct(string $path, $fallback = NULL) {
    $keywords = ['$data' => $path];
    if (NULL !== $fallback) {
      $keywords['fallback'] = $fallback;
    }

    parent::__construct($keywords);
  }

}
