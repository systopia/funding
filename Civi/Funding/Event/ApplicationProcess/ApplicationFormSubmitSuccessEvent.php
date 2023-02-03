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

namespace Civi\Funding\Event\ApplicationProcess;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Form\ValidatedApplicationDataInterface;

final class ApplicationFormSubmitSuccessEvent extends AbstractApplicationEvent {

  /**
   * @phpstan-var array<string, mixed>
   */
  private array $submittedData;

  private ValidatedApplicationDataInterface $validatedData;

  /**
   * @phpstan-param array<string, mixed> $submittedData
   */
  public function __construct(
    int $contactId,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $submittedData,
    ValidatedApplicationDataInterface $validatedData
  ) {
    parent::__construct($contactId, $applicationProcessBundle);
    $this->submittedData = $submittedData;
    $this->validatedData = $validatedData;
  }

  public function getAction(): string {
    return $this->validatedData->getAction();
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getSubmittedData(): array {
    return $this->submittedData;
  }

  public function getValidatedData(): ValidatedApplicationDataInterface {
    return $this->validatedData;
  }

}
