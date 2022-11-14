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

namespace Civi\Funding\ApplicationProcess\Command;

use Civi\Funding\Form\Validation\ValidationResult;

final class ApplicationFormValidateResult {

  private ValidationResult $validationResult;

  public static function create(ValidationResult $validationResult): self {
    return new self($validationResult);
  }

  private function __construct(ValidationResult $validationResult) {
    $this->validationResult = $validationResult;
  }

  /**
   * @phpstan-return array<string, mixed> JSON serializable.
   */
  public function getData(): array {
    return $this->validationResult->getData();
  }

  /**
   * @phpstan-return array<string, non-empty-array<string>>
   *   JSON pointers mapped to error messages.
   */
  public function getErrors(): array {
    return $this->validationResult->getLeafErrorMessages();
  }

  public function isValid(): bool {
    return $this->validationResult->isValid();
  }

}
