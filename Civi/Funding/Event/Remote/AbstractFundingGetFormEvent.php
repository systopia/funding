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

use Civi\Funding\Event\Remote\Traits\EventContactIdRequiredTrait;
use Civi\RemoteTools\Event\AbstractRequestEvent;

abstract class AbstractFundingGetFormEvent extends AbstractRequestEvent {

  use EventContactIdRequiredTrait;

  /**
   * @var array<string, mixed>
   */
  private array $jsonSchema = [];

  /**
   * @var array<string, mixed>
   */
  private array $uiSchema = [];

  /**
   * @var array<string, mixed>
   */
  private array $data = [];

  /**
   * @return array<string, mixed>
   */
  public function getJsonSchema(): array {
    return $this->jsonSchema;
  }

  /**
   * @param array<string, mixed> $jsonSchema
   */
  public function setJsonSchema(array $jsonSchema): self {
    $this->jsonSchema = $jsonSchema;

    return $this;
  }

  /**
   * @return array<string, mixed>
   */
  public function getUiSchema(): array {
    return $this->uiSchema;
  }

  /**
   * @param array<string, mixed> $uiSchema
   */
  public function setUiSchema(array $uiSchema): self {
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
