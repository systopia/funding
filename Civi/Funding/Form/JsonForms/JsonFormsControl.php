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

namespace Civi\Funding\Form\JsonForms;

use Civi\Funding\Form\JsonSchema\JsonSchema;

class JsonFormsControl extends JsonFormsElement {

  /**
   * @param string $scope
   * @param string $label
   * @param string|null $description
   * @param string|null $prefix
   * @param string|null $suffix
   * @param array<string, mixed>|null $options
   */
  public function __construct(string $scope, string $label,
    ?string $description = NULL, ?string $prefix = NULL, ?string $suffix = NULL, ?array $options = NULL) {
    $keywords = [
      'scope' => $scope,
      'label' => $label,
    ];
    if (NULL !== $description) {
      $keywords['description'] = $description;
    }
    if (NULL !== $prefix) {
      $keywords['prefix'] = $prefix;
    }
    if (NULL !== $suffix) {
      $keywords['suffix'] = $suffix;
    }
    if (NULL !== $options) {
      $keywords['options'] = JsonSchema::fromArray($options);
    }

    parent::__construct('Control', $keywords);
  }

  public function getScope(): string {
    /** @var string $scope */
    $scope = $this->keywords['scope'];

    return $scope;
  }

}
