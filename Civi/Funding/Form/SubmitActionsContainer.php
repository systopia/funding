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

namespace Civi\Funding\Form;

use Webmozart\Assert\Assert;

final class SubmitActionsContainer {

  /**
   * @phpstan-var array<string, array{label: string, confirm: string|null}>
   */
  private array $actions = [];

  private int $nextPriority = 100;

  /**
   * @phpstan-var array<string, int>
   */
  private array $priorities = [];

  /**
   * If no priority is given, the order of adding determines the priority, i.e.
   * the priority is one less than the previous least priority.
   */
  public function add(string $action, string $label, ?string $confirm = NULL, int $priority = NULL): self {
    $this->actions[$action] = ['label' => $label, 'confirm' => $confirm];
    $priority ??= $this->nextPriority;
    $this->priorities[$action] = $this->nextPriority;
    $this->nextPriority = min($priority - 1, $this->nextPriority);

    return $this;
  }

  /**
   * @phpstan-return array{label: string, confirm: string|null}
   */
  public function get(string $action): array {
    $this->assertActionExists($action);

    return $this->actions[$action];
  }

  public function getPriority(string $action): int {
    $this->assertActionExists($action);

    return $this->priorities[$action];
  }

  public function has(string $action): bool {
    return isset($this->actions[$action]);
  }

  private function assertActionExists(string $action): void {
    Assert::keyExists($this->actions, $action, sprintf('Unknown submit action "%s"', $action));
  }

}