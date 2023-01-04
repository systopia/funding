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
use Civi\Funding\Entity\Traits\ApplicationProcessEntityBundleTrait;

final class ApplicationFormValidateCommand {

  use ApplicationProcessEntityBundleTrait;

  /**
   * @phpstan-var array<string, mixed> JSON serializable.
   */
  private array $data;

  /**
   * @phpstan-param array<string, mixed> $data JSON serializable.
   */
  public function __construct(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $data
  ) {
    $this->applicationProcessBundle = $applicationProcessBundle;
    $this->data = $data;
  }

  /**
   * @phpstan-return array<string, mixed> JSON serializable.
   */
  public function getData(): array {
    return $this->data;
  }

}
