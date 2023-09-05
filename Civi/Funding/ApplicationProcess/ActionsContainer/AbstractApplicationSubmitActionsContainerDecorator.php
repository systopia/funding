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

namespace Civi\Funding\ApplicationProcess\ActionsContainer;

use Civi\Funding\Form\SubmitActionsContainerInterface;

// phpcs:disable Generic.Files.LineLength.TooLong
abstract class AbstractApplicationSubmitActionsContainerDecorator implements ApplicationSubmitActionsContainerInterface {
// phpcs:enable
  protected ApplicationSubmitActionsContainerInterface $submitActionsContainer;

  public function __construct(ApplicationSubmitActionsContainerInterface $submitActionsContainer) {
    $this->submitActionsContainer = $submitActionsContainer;
  }

  /**
   * @inheritDoc
   */
  public function add(
    string $action,
    string $label,
    ?string $confirm = NULL,
    array $properties = [],
    int $priority = NULL
  ): SubmitActionsContainerInterface {
    $this->submitActionsContainer->add($action, $label, $confirm, $properties, $priority);

    return $this;
  }

  /**
   * @inheritDoc
   */
  public function get(string $action): array {
    return $this->submitActionsContainer->get($action);
  }

  public function getPriority(string $action): int {
    return $this->submitActionsContainer->getPriority($action);
  }

  public function has(string $action): bool {
    return $this->submitActionsContainer->has($action);
  }

}
