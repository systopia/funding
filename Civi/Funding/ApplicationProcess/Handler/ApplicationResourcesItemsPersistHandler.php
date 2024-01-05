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

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsPersistCommand;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;

final class ApplicationResourcesItemsPersistHandler implements ApplicationResourcesItemsPersistHandlerInterface {

  private ApplicationResourcesItemManager $resourcesItemManager;

  public function __construct(ApplicationResourcesItemManager $resourcesItemManager) {
    $this->resourcesItemManager = $resourcesItemManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(ApplicationResourcesItemsPersistCommand $command): void {
    $resourcesItems = [];
    foreach ($command->getResourcesItemsData() as $resourcesItemData) {
      $resourcesItems[] = ApplicationResourcesItemEntity::fromArray([
        'application_process_id' => $command->getApplicationProcess()->getId(),
        'identifier' => $resourcesItemData->getIdentifier(),
        'type' => $resourcesItemData->getType(),
        'amount' => $resourcesItemData->getAmount(),
        'properties' => $resourcesItemData->getProperties(),
        'data_pointer' => $resourcesItemData->getDataPointer(),
      ]);
    }

    $this->resourcesItemManager->updateAll($command->getApplicationProcess()->getId(), $resourcesItems);
  }

}
