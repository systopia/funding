<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Form;

use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;

/**
 * @codeCoverageIgnore
 */
class JsonFormsFormWithData implements JsonFormsFormWithDataInterface {

  /**
   * @var array<string, mixed>
   */
  private array $data;

  private JsonSchema $jsonSchema;

  private JsonFormsElement $uiSchema;

  /**
   * @phpstan-param array<string, mixed> $data
   */
  public function __construct(JsonSchema $jsonSchema, JsonFormsElement $uiSchema, array $data = []) {
    $this->data = $data;
    $this->jsonSchema = $jsonSchema;
    $this->uiSchema = $uiSchema;
  }

  /**
   * @inheritDoc
   */
  public function getData(): array {
    return $this->data;
  }

  /**
   * @inheritDoc
   */
  public function setData(array $data): JsonFormsFormWithDataInterface {
    $this->data = $data;

    return $this;
  }

  public function getJsonSchema(): JsonSchema {
    return $this->jsonSchema;
  }

  public function getUiSchema(): JsonFormsElement {
    return $this->uiSchema;
  }

}
