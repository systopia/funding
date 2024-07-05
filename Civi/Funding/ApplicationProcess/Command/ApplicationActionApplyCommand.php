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

use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\Traits\ApplicationProcessEntityBundleTrait;
use Webmozart\Assert\Assert;

final class ApplicationActionApplyCommand {

  use ApplicationProcessEntityBundleTrait;

  private int $contactId;

  private string $action;

  private ?ApplicationFormValidationResult $validationResult;

  /**
   * @param \Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidationResult|null $validationResult
   *   A valid result or NULL.
   */
  public function __construct(
    int $contactId,
    string $action,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ?ApplicationFormValidationResult $validationResult
  ) {
    $this->contactId = $contactId;
    $this->action = $action;
    $this->applicationProcessBundle = $applicationProcessBundle;
    if (NULL !== $validationResult) {
      Assert::true($validationResult->isValid());
    }
    $this->validationResult = $validationResult;
  }

  public function getAction(): string {
    return $this->action;
  }

  public function getContactId(): int {
    return $this->contactId;
  }

  public function getValidationResult(): ?ApplicationFormValidationResult {
    return $this->validationResult;
  }

}
