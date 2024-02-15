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
use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;
use Civi\Funding\Entity\ApplicationCostItemEntity;

final class ApplicationCostItemsPersistHandler implements ApplicationCostItemsPersistHandlerInterface {

  private ApplicationCostItemManager $costItemManager;

  public function __construct(ApplicationCostItemManager $costItemManager) {
    $this->costItemManager = $costItemManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ApplicationCostItemsPersistCommand $command): void {
    $costItems = [];
    foreach ($command->getCostItemsData() as $costItemData) {
      $costItems[] = ApplicationCostItemEntity::fromArray([
        'application_process_id' => $command->getApplicationProcess()->getId(),
        'identifier' => $costItemData->getIdentifier(),
        'type' => $costItemData->getType(),
        'amount' => $costItemData->getAmount(),
        'properties' => $costItemData->getProperties(),
        'data_pointer' => $costItemData->getDataPointer(),
      ]);
    }

    $this->costItemManager->updateAll($command->getApplicationProcess()->getId(), $costItems);
  }

}
