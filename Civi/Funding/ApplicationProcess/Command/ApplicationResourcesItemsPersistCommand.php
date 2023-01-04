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

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\Traits\ApplicationProcessEntityBundleTrait;

final class ApplicationResourcesItemsPersistCommand {

  use ApplicationProcessEntityBundleTrait;

  private ?ApplicationProcessEntity $previousApplicationProcess;

  public function __construct(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    ?ApplicationProcessEntity $previousApplicationProcess
  ) {
    $this->applicationProcessBundle = $applicationProcessBundle;
    $this->previousApplicationProcess = $previousApplicationProcess;
  }

  public function getPreviousApplicationProcess(): ?ApplicationProcessEntity {
    return $this->previousApplicationProcess;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function getRequestData(): array {
    return $this->getApplicationProcess()->getRequestData();
  }

  /**
   * @phpstan-return ?array<string, mixed> The previous request data or null.
   */
  public function getPreviousRequestData(): ?array {
    return NULL === $this->previousApplicationProcess ? NULL : $this->previousApplicationProcess->getRequestData();
  }

}
