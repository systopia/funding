<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Form\Validation;

use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;

/**
 * @codeCoverageIgnore
 */
final class ClearingFormValidationResult {

  /**
   * @phpstan-var array<string, non-empty-list<string>>
   */
  private array $errorMessages;

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data;

  private TaggedDataContainerInterface $taggedData;

  /**
   * @phpstan-param array<string, non-empty-list<string>> $errorMessages
   * @phpstan-param array<string, mixed> $data
   */
  public function __construct(array $errorMessages, array $data, TaggedDataContainerInterface $taggedData) {
    $this->errorMessages = $errorMessages;
    $this->data = $data;
    $this->taggedData = $taggedData;
  }

  /**
   * @phpstan-return array<string, non-empty-list<string>>
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

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  public function getTaggedData(): TaggedDataContainerInterface {
    return $this->taggedData;
  }

}
