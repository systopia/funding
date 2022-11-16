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

use Civi\Funding\Form\ValidatedApplicationDataInterface;
use Civi\Funding\Form\Validation\ValidationResult;

final class ApplicationFormSubmitResult {

  private bool $success;

  private ?ValidatedApplicationDataInterface $validatedData;

  private ValidationResult $validationResult;

  public static function createError(ValidationResult $validationResult): self {
    return new self(FALSE, $validationResult);
  }

  public static function createSuccess(
    ValidationResult $validationResult,
    ValidatedApplicationDataInterface $validatedData
  ): self {
    return new self(TRUE, $validationResult, $validatedData);
  }

  private function __construct(
    bool $success,
    ValidationResult $validationResult,
    ?ValidatedApplicationDataInterface $validatedData = NULL
  ) {
    $this->success = $success;
    $this->validationResult = $validationResult;
    $this->validatedData = $validatedData;
  }

  public function isSuccess(): bool {
    return $this->success;
  }

  public function getValidatedData(): ?ValidatedApplicationDataInterface {
    return $this->validatedData;
  }

  public function getValidationResult(): ValidationResult {
    return $this->validationResult;
  }

}