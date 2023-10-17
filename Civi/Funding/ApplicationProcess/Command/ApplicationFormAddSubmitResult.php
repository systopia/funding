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

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Form\Application\ApplicationValidationResult;

final class ApplicationFormAddSubmitResult extends AbstractApplicationFormSubmitResult {

  private ?ApplicationProcessEntityBundle $applicationProcessBundle;

  public static function createError(ApplicationValidationResult $validationResult): self {
    return new self(FALSE, $validationResult);
  }

  public static function createSuccess(
    ApplicationValidationResult $validationResult,
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): self {
    return new self(TRUE, $validationResult, $applicationProcessBundle);
  }

  protected function __construct(
    bool $success,
    ApplicationValidationResult $validationResult,
    ApplicationProcessEntityBundle $applicationProcessBundle = NULL
  ) {
    parent::__construct($success, $validationResult);
    $this->applicationProcessBundle = $applicationProcessBundle;
  }

  public function getApplicationProcessBundle(): ?ApplicationProcessEntityBundle {
    return $this->applicationProcessBundle;
  }

}
