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
 * @phpstan-type fundingCaseActionT array{
 *   name: string,
 *   label: string,
 *   confirmMessage?: string|null,
 * }
 *   Defaults:
 *     - confirmMessage: NULL
 *
 * The priority is used to determine the order in the form for applicants (not
 * relevant for review, yet).
 */
final class FundingCaseAction {

  /**
   * @phpstan-var fundingCaseActionT
   */
  private array $values;

  /**
   * @phpstan-param fundingCaseActionT $values
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

}
