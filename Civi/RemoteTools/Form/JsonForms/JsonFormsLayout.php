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

namespace Civi\RemoteTools\Form\JsonForms;

use Civi\RemoteTools\Form\JsonSchema\JsonSchema;

class JsonFormsLayout extends JsonFormsElement {

  /**
   * @param string $type
   * @param string $label
   * @param array<int, JsonFormsElement> $elements
   * @param string|null $description
   */
  public function __construct(string $type, string $label, array $elements, ?string $description = NULL) {
    $keywords = [
      'label' => $label,
      'elements' => JsonSchema::convertToJsonSchemaArray($elements),
    ];
    if (NULL !== $description) {
      $keywords['description'] = $description;
    }

    parent::__construct($type, $keywords);
  }

  /**
   * @return array<int, JsonFormsElement>
   */
  public function getElements(): array {
    /** @var array<int, JsonFormsElement> $elements */
    $elements = $this->keywords['elements'];

    return $elements;
  }

  public function isReadonly(): ?bool {
    /** @var bool|null */
    return $this->keywords['options']->keywords['readonly'] ?? NULL;
  }

  public function setReadonly(bool $readonly): self {
    /** @var \Civi\RemoteTools\Form\JsonSchema\JsonSchema $options */
    $options = $this->keywords['options'] ??= new JsonSchema([]);
    $options->addKeyword('readonly', $readonly);

    return $this;
  }

}
