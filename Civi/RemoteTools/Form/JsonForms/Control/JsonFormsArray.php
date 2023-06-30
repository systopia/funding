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

namespace Civi\RemoteTools\Form\JsonForms\Control;

use Civi\RemoteTools\Form\JsonForms\JsonFormsControl;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;

class JsonFormsArray extends JsonFormsControl {

  /**
   * @phpstan-param array<int, JsonFormsControl>|null $elements
   *   The elements of each array entry to display. If NULL, all elements are
   *   shown based on the JSON schema.
   * @phpstan-param array{
   *   addButtonLabel?: string,
   *   removeButtonLabel?: string,
   *   detail?: array{type: string},
   * }|null $options
   *   "type" in "detail" is the layout type, e.g. HorizontalLayout.
   */
  public function __construct(
    string $scope,
    string $label,
    ?string $description = NULL,
    ?array $elements = NULL,
    ?array $options = NULL
  ) {
    if (NULL !== $elements) {
      $options['detail'] ??= [];
      $options['detail']['elements'] = $elements;
    }

    parent::__construct($scope, $label, $description, NULL, NULL, $options);
  }

  /**
   * @return array<int, JsonFormsControl>|null
   */
  public function getElements(): ?array {
    $options = $this->keywords['options'] ?? NULL;
    if (!$options instanceof JsonSchema) {
      return NULL;
    }
    $detail = $options->keywords['detail'] ?? NULL;
    if (!$detail instanceof JsonSchema) {
      return NULL;
    }

    /** @var array<int, JsonFormsControl>|null $elements */
    $elements = $detail->keywords['elements'] ?? NULL;

    return $elements;
  }

}
