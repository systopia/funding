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

namespace Civi\RemoteTools\Form;

use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchema;

/**
 * @codeCoverageIgnore
 */
class RemoteForm implements RemoteFormInterface {

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
  public function setData(array $data): RemoteFormInterface {
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
