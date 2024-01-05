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

abstract class AbstractFundingValidateFormEvent extends AbstractFundingRequestEvent {

  /**
   * @var array<string, mixed>
   */
  protected array $data;

  /**
   * @var array<string, string[]>
   */
  private array $errors = [];

  private ?bool $valid = NULL;

  /**
   * @return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  public function addError(string $jsonPointer, string $message): self {
    $this->addErrorsAt($jsonPointer, [$message]);

    return $this;
  }

  /**
   * @param string $jsonPointer
   * @param non-empty-list<string> $messages
   */
  public function addErrorsAt(string $jsonPointer, array $messages): self {
    $this->errors[$jsonPointer] = array_merge($this->errors[$jsonPointer] ?? [], $messages);
    $this->valid = FALSE;

    return $this;
  }

  /**
   * @return array<string, string[]>
   */
  public function getErrors(): array {
    return $this->errors;
  }

  public function isValid(): ?bool {
    return $this->valid;
  }

  public function setValid(bool $valid): self {
    $this->valid = $valid;

    return $this;
  }

  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), ['data']);
  }

}
