<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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

namespace Civi\Funding\FundingCaseType\MetaData;

/**
 * @phpstan-type applicationProcessActionT array{
 *   name: string,
 *   label: string,
 *   confirmMessage?: string|null,
 *   batchPossible?: bool,
 *   delete?: bool,
 *   restore?: bool,
 * }
 *   Defaults:
 *     - confirmMessage: NULL
 *     - batchPossible: FALSE
 *     - delete: FALSE
 *     - restore: FALSE
 *
 * If "batchPossible" is TRUE, the action can be applied to multiple application
 * processes at the same time, i.e. it can be performed without form data. It
 * will be available as SearchKit action for reviewers.
 *
 * If "delete" is TRUE the application process will be deleted when the action
 * is performed.
 *
 * If "restore" is TRUE, the application process will be restored to the last
 * snapshot when the action is performed.
 */
final class ApplicationProcessAction {

  /**
   * @phpstan-var applicationProcessActionT
   */
  private array $values;

  /**
   * @phpstan-param applicationProcessActionT $values
   */
  public function __construct(array $values) {
    $this->values = $values;
  }

  public function getName(): string {
    return $this->values['name'];
  }

  public function getLabel(): string {
    return $this->values['label'];
  }

  public function getConfirmMessage(): ?string {
    return $this->values['confirmMessage'] ?? NULL;
  }

  /**
   * If TRUE, action can be applied to multiple applications at the same time,
   * i.e. it can be performed without form data.
   */
  public function isBatchPossible(): bool {
    return $this->values['batchPossible'] ?? FALSE;
  }

  public function isDelete(): bool {
    return $this->values['delete'] ?? FALSE;
  }

  public function isRestore(): bool {
    return $this->values['restore'] ?? FALSE;
  }

}
