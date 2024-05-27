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

namespace Civi\Funding\Form;

use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonForms\Layout\JsonFormsGroup;
use Civi\RemoteTools\JsonSchema\JsonSchema;

/**
 * @codeCoverageIgnore
 */
class JsonFormsForm implements JsonFormsFormInterface {

  private JsonSchema $jsonSchema;

  private JsonFormsElement $uiSchema;

  public static function newEmpty(): self {
    return new self(new JsonSchema([]), new JsonFormsGroup('', []));
  }

  public function __construct(JsonSchema $jsonSchema, JsonFormsElement $uiSchema) {
    $this->jsonSchema = $jsonSchema;
    $this->uiSchema = $uiSchema;
  }

  public function getJsonSchema(): JsonSchema {
    return $this->jsonSchema;
  }

  public function getUiSchema(): JsonFormsElement {
    return $this->uiSchema;
  }

}
