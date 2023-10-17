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

namespace Civi\Funding\ApplicationProcess\ActionsDeterminer;

use Civi\Funding\Entity\FullApplicationProcessStatus;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractApplicationActionsDeterminerDecorator implements ApplicationProcessActionsDeterminerInterface {

  private ApplicationProcessActionsDeterminerInterface $actionsDeterminer;

  public function __construct(ApplicationProcessActionsDeterminerInterface $actionsDeterminer) {
    $this->actionsDeterminer = $actionsDeterminer;
  }

  /**
   * @inheritDoc
   */
  public function getActions(FullApplicationProcessStatus $status, array $statusList, array $permissions): array {
    return $this->actionsDeterminer->getActions($status, $statusList, $permissions);
  }

  /**
   * @inheritDoc
   */
  public function getInitialActions(array $permissions): array {
    return $this->actionsDeterminer->getInitialActions($permissions);
  }

  /**
   * @inheritDoc
   */
  public function isActionAllowed(
    string $action,
    FullApplicationProcessStatus $status,
    array $statusList,
    array $permissions
  ): bool {
    return $this->actionsDeterminer->isActionAllowed($action, $status, $statusList, $permissions);
  }

  /**
   * @inheritDoc
   */
  public function isAnyActionAllowed(
    array $actions,
    FullApplicationProcessStatus $status,
    array $statusList,
    array $permissions
  ): bool {
    return $this->actionsDeterminer->isAnyActionAllowed($actions, $status, $statusList, $permissions);
  }

  /**
   * @inheritDoc
   */
  public function isEditAllowed(FullApplicationProcessStatus $status, array $statusList, array $permissions): bool {
    return $this->actionsDeterminer->isEditAllowed($status, $statusList, $permissions);
  }

}
