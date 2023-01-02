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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationCostItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;

final class ApplicationCostItemsPersistHandler implements ApplicationCostItemsPersistHandlerInterface {

  private ApplicationCostItemsFactoryInterface $costItemsFactory;

  private ApplicationCostItemManager $costItemManager;

  public function __construct(
    ApplicationCostItemsFactoryInterface $costItemsFactory,
    ApplicationCostItemManager $costItemManager
  ) {
    $this->costItemsFactory = $costItemsFactory;
    $this->costItemManager = $costItemManager;
  }

  /**
   * @throws \API_Exception
   */
  public function handle(ApplicationCostItemsPersistCommand $command): void {
    if (NULL === $command->getPreviousRequestData()
      || $this->costItemsFactory->areCostItemsChanged($command->getRequestData(), $command->getPreviousRequestData())
    ) {
      $items = $this->costItemsFactory->createItems($command->getApplicationProcess());
      $this->costItemManager->updateAll($command->getApplicationProcess()->getId(), $items);
    }
  }

}
