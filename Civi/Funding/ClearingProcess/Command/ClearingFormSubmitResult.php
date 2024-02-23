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

namespace Civi\Funding\ClearingProcess\Command;

final class ClearingFormSubmitResult {

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $data;

  /**
   * @phpstan-var array<string, non-empty-list<string>>
   */
  private array $errors;

  /**
   * @phpstan-var array<string, string>
   */
  private array $files;

  /**
   * @phpstan-param array<string, mixed> $data
   * @phpstan-param array<string, non-empty-list<string>> $errors
   * @phpstan-param array<string, string> $files
   */
  public function __construct(array $data, array $errors, array $files) {
    $this->data = $data;
    $this->errors = $errors;
    $this->files = $files;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getData(): array {
    return $this->data;
  }

  /**
   * @phpstan-return array<string, non-empty-list<string>>
   */
  public function getErrors(): array {
    return $this->errors;
  }

  /**
   * @phpstan-return array<string, string>
   */
  public function getFiles(): array {
    return $this->files;
  }

}
