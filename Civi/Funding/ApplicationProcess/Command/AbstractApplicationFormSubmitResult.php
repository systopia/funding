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

use Civi\Funding\Form\ApplicationValidationResult;
use Civi\Funding\Form\ValidatedApplicationDataInterface;

abstract class AbstractApplicationFormSubmitResult {

  /**
   * @phpstan-var array<\Civi\Funding\Entity\ExternalFileEntity>
   */
  protected array $files;

  protected bool $success;

  protected ApplicationValidationResult $validationResult;

  /**
   * @phpstan-param array<string, \Civi\Funding\Entity\ExternalFileEntity> $files
   *   Mapping of URI from request to file.
   */
  protected function __construct(
    bool $success,
    ApplicationValidationResult $validationResult,
    array $files = []
  ) {
    $this->success = $success;
    $this->validationResult = $validationResult;
    $this->files = $files;
  }

  public function isSuccess(): bool {
    return $this->success;
  }

  /**
   * @phpstan-return array<string, \Civi\Funding\Entity\ExternalFileEntity>
   *   Mapping of URI from request to file.
   */
  public function getFiles(): array {
    return $this->files;
  }

  /**
   * @phpstan-param array<string, \Civi\Funding\Entity\ExternalFileEntity> $files
   *   Mapping of URI from request to file.
   */
  public function setFiles(array $files): self {
    $this->files = $files;

    return $this;
  }

  /**
   * The returned data might not contain the actual application data if the
   * requested action resulted in a restore of a previous snapshot.
   */
  public function getValidatedData(): ValidatedApplicationDataInterface {
    return $this->validationResult->getValidatedData();
  }

  public function getValidationResult(): ApplicationValidationResult {
    return $this->validationResult;
  }

}
