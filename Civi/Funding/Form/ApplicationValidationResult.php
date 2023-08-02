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

namespace Civi\Funding\Form;

use Webmozart\Assert\Assert;

final class ApplicationValidationResult {

  /**
   * @phpstan-var array<string, non-empty-array<string>>
   */
  private array $errorMessages;

  private bool $readOnly;

  private ValidatedApplicationDataInterface $validatedData;

  /**
   * @phpstan-param non-empty-array<string, non-empty-array<string>> $errorMessages
   *   JSON pointers mapped to error messages. Should only contain leaf errors.
   */
  public static function newInvalid(
    array $errorMessages,
    ValidatedApplicationDataInterface $validatedData
  ): self {
    Assert::notEmpty($errorMessages);

    return new self($errorMessages, $validatedData, TRUE);
  }

  public static function newValid(ValidatedApplicationDataInterface $validatedData, bool $readOnly): self {
    return new self([], $validatedData, $readOnly);
  }

  /**
   * @phpstan-param array<string, non-empty-array<string>> $errorMessages
   */
  private function __construct(
    array $errorMessages,
    ValidatedApplicationDataInterface $validatedData,
    bool $readOnly
  ) {
    $this->errorMessages = $errorMessages;
    $this->validatedData = $validatedData;
    $this->readOnly = $readOnly;
  }

  /**
   * @phpstan-return array<string, non-empty-array<string>>
   *   JSON pointers mapped to error messages.
   */
  public function getErrorMessages(): array {
    return $this->errorMessages;
  }

  public function hasErrors(): bool {
    return [] !== $this->errorMessages;
  }

  public function isValid(): bool {
    return [] === $this->errorMessages;
  }

  public  function getValidatedData(): ValidatedApplicationDataInterface {
    return $this->validatedData;
  }

  /**
   * @return bool TRUE if the validated data shall not be persisted.
   */
  public  function isReadOnly(): bool {
    return $this->readOnly;
  }

}
