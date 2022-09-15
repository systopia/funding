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

namespace Civi\Funding\Event\Remote;

use Civi\RemoteTools\Form\JsonForms\JsonFormsElement;
use Civi\RemoteTools\Form\JsonSchema\JsonSchema;

abstract class AbstractFundingGetFormEvent extends AbstractFundingRequestEvent {

  private ?JsonSchema $jsonSchema = NULL;

  private ?JsonFormsElement $uiSchema = NULL;

  /**
   * @var array<string, mixed>
   */
  private array $data = [];

  public function getJsonSchema(): ?JsonSchema {
    return $this->jsonSchema;
  }

  public function setJsonSchema(JsonSchema $jsonSchema): self {
    $this->jsonSchema = $jsonSchema;

    return $this;
  }

  public function getUiSchema(): ?JsonFormsElement {
    return $this->uiSchema;
  }

  public function setUiSchema(JsonFormsElement $uiSchema): self {
    $this->uiSchema = $uiSchema;

    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  /**
   * @param array<string, mixed> $data
   */
  public function setData(array $data): self {
    $this->data = $data;

    return $this;
  }

}
