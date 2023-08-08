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

final class ApplicationFormSubmitCommand {

  use ApplicationProcessEntityBundleTrait;

  /**
   * @phpstan-var array<int, \Civi\Funding\Entity\FullApplicationProcessStatus>
   */
  private array $applicationProcessStatusList;

  private int $contactId;

  /**
   * @phpstan-var array<string, mixed> JSON serializable.
   */
  private array $data;

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $applicationProcessStatusList
   *     Status of other application processes in same funding case indexed by ID.
   * @phpstan-param array<string, mixed> $data JSON serializable.
   */
  public function __construct(
    int $contactId,
    ApplicationProcessEntityBundle $applicationProcessBundle,
    array $applicationProcessStatusList,
    array $data
  ) {
    $this->contactId = $contactId;
    $this->applicationProcessBundle = $applicationProcessBundle;
    $this->applicationProcessStatusList = $applicationProcessStatusList;
    $this->data = $data;
  }

  /**
   * @phpstan-return array<int, \Civi\Funding\Entity\FullApplicationProcessStatus>
   *   Status of other application processes in same funding case indexed by ID.
   */
  public function getApplicationProcessStatusList(): array {
    return $this->applicationProcessStatusList;
  }

  public function getContactId(): int {
    return $this->contactId;
  }

  /**
   * @phpstan-return array<string, mixed> JSON serializable.
   */
  public function getData(): array {
    return $this->data;
  }

}
