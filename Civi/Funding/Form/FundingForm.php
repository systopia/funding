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

namespace Civi\Funding\Form;

use Civi\Funding\Form\JsonForms\JsonFormsElement;
use Civi\Funding\Form\JsonSchema\JsonSchema;

class FundingForm implements FundingFormInterface {

  /**
   * @var array<string, mixed>
   */
  private array $data;

  private JsonSchema $jsonSchema;

  private JsonFormsElement $uiSchema;

  /**
   * @param \Civi\Funding\Form\JsonSchema\JsonSchema $jsonSchema
   * @param \Civi\Funding\Form\JsonForms\JsonFormsElement $uiSchema
   * @param array<string, mixed> $data
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

  public function getJsonSchema(): JsonSchema {
    return $this->jsonSchema;
  }

  public function getUiSchema(): JsonFormsElement {
    return $this->uiSchema;
  }

}
