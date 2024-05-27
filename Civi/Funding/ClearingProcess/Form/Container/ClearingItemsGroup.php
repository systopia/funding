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

namespace Civi\Funding\ClearingProcess\Form\Container;

final class ClearingItemsGroup {

  /**
   * @var string|null
   *   If not NULL, a JSON forms group shall be created.
   */
  public ?string $label;

  /**
   * @phpstan-var array<string, \Civi\RemoteTools\JsonSchema\JsonSchema>
   *   JSON Forms elements of the application form mapped by their scopes.
   */
  public array $elements = [];

  /**
   * @phpstan-param array<string, \Civi\RemoteTools\JsonSchema\JsonSchema> $elements
   */
  public function __construct(?string $label, array $elements = []) {
    $this->label = $label;
    $this->elements = $elements;
  }

}
