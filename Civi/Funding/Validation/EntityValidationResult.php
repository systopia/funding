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

namespace Civi\Funding\Validation;

use Civi\Funding\Validation\Exception\EntityValidationFailedException;
use CRM_Funding_ExtensionUtil as E;

final class EntityValidationResult {

  /**
   * @phpstan-var array<string, array<EntityValidationError>>
   */
  private array $errors = [];

  public static function new(EntityValidationError ...$errors): self {
    return new self(...$errors);
  }

  public function __construct(EntityValidationError ...$errors) {
    $this->addErrors(...$errors);
  }

  public function addErrors(EntityValidationError ...$errors): self {
    foreach ($errors as $error) {
      $this->addError($error);
    }

    return $this;
  }

  public function addError(EntityValidationError $error): self {
    if (isset($this->errors[$error->field])) {
      $this->errors[$error->field][] = $error;
    }
    else {
      $this->errors[$error->field] = [$error];
    }

    return $this;
  }

  /**
   * @phpstan-return array<string, array<EntityValidationError>>
   *   Field names mapped to errors.
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * @phpstan-return array<EntityValidationError>
   */
  public function getErrorsFlat(): array {
    $errors = [];
    foreach ($this->errors as $fieldErrors) {
      foreach ($fieldErrors as $error) {
        $errors[] = $error;
      }
    }

    return $errors;
  }

  public function hasErrors(): bool {
    return [] !== $this->errors;
  }

  /**
   * @phpstan-return array<EntityValidationError>
   */
  public function getErrorsFor(string $field): array {
    return $this->errors[$field] ?? [];
  }

  public function hasErrorsFor(string $field): bool {
    return isset($this->errors[$field]);
  }

  public function isValid(): bool {
    return [] === $this->errors;
  }

  public function merge(EntityValidationResult $result): self {
    $this->addErrors(...$result->getErrorsFlat());

    return $this;
  }

  public function toException(): EntityValidationFailedException {
    if ($this->isValid()) {
      throw new \RuntimeException('No errors in validation result');
    }

    $errorMessages = implode(' ', array_map(
      fn (EntityValidationError $error) => $error->message,
      $this->getErrorsFlat()
    ));

    return new EntityValidationFailedException(E::ts('Validation failed: %1', [1 => $errorMessages]));
  }

}
