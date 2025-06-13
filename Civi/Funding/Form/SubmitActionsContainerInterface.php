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

namespace Civi\Funding\Form;

interface SubmitActionsContainerInterface {

  /**
   * If no priority is given, the order of adding determines the priority, i.e.
   * the priority is one less than the previous least priority.
   *
   * @phpstan-param array<string, mixed>&array{needsFormData?: bool} $properties
   *   needsFormData is FALSE if the action is applicable without form data.
   *
   * @return $this
   */
  public function add(
    string $action,
    string $label,
    ?string $confirm = NULL,
    array $properties = [],
    ?int $priority = NULL
  ): self;

  /**
   * @phpstan-return array{
   *   label: string,
   *   confirm: string|null,
   *   properties: array<string, mixed>&array{needsFormData?: bool},
   * }
   *   needsFormData is FALSE if the action is applicable without form data.
   */
  public function get(string $action): array;

  public function getPriority(string $action): int;

  public function has(string $action): bool;

}
