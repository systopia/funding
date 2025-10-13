<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\PayoutProcess\Task;

use Civi\Funding\Entity\DrawdownBundle;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Task\Creator\DrawdownTaskCreatorInterface;

/**
 * Should be combined with:
 * @see \Civi\Funding\PayoutProcess\Task\AbstractDrawdownCreateTaskHandler
 */
abstract class AbstractDrawdownCreateTaskCreator implements DrawdownTaskCreatorInterface {

  use DrawdownCreateTaskTrait;

  /**
   * @phpstan-return list<string>
   */
  abstract public static function getSupportedFundingCaseTypes(): array;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(PayoutProcessManager $payoutProcessManager) {
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @inheritDoc
   */
  public function createTasksOnChange(DrawdownBundle $drawdownBundle, DrawdownEntity $previousDrawdown): iterable {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function createTasksOnNew(DrawdownBundle $drawdownBundle): iterable {
    return [];
  }

  /**
   * @inheritDoc
   */
  public function createTasksOnDelete(DrawdownBundle $drawdownBundle): iterable {
    if ($this->payoutProcessManager->getAmountAvailable($drawdownBundle->getPayoutProcess()) > 0) {
      yield $this->createCreateTask($drawdownBundle);
    }
  }

}
