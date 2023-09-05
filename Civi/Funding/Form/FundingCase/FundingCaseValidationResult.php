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

namespace Civi\Funding\Form\FundingCase;

use Webmozart\Assert\Assert;

/**
 * @codeCoverageIgnore
 */
final class FundingCaseValidationResult {

  /**
   * @phpstan-var array<string, non-empty-array<string>>
   */
  private array $errorMessages;

  private ValidatedFundingCaseDataInterface $validatedData;

  /**
   * @phpstan-param non-empty-array<string, non-empty-array<string>> $errorMessages
   *   JSON pointers mapped to error messages. Should only contain leaf errors.
   */
  public static function newInvalid(
    array $errorMessages,
    ValidatedFundingCaseDataInterface $validatedData
  ): self {
    Assert::notEmpty($errorMessages);

    return new self($errorMessages, $validatedData);
  }

  public static function newValid(ValidatedFundingCaseDataInterface $validatedData): self {
    return new self([], $validatedData);
  }

  /**
   * @phpstan-param array<string, non-empty-array<string>> $errorMessages
   */
  private function __construct(
    array $errorMessages,
    ValidatedFundingCaseDataInterface $validatedData
  ) {
    $this->errorMessages = $errorMessages;
    $this->validatedData = $validatedData;
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

  public  function getValidatedData(): ValidatedFundingCaseDataInterface {
    return $this->validatedData;
  }

}
