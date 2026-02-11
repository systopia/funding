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

namespace Civi\Funding\Form\Application;

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Util\ArrayUtil;
use Opis\JsonSchema\JsonPointer;

final class ApplicationResourcesItemsFormDataLoader implements ApplicationResourcesItemsFormDataLoaderInterface {

  private ApplicationResourcesItemManager $resourcesItemManager;

  public function __construct(ApplicationResourcesItemManager $resourcesItemManager) {
    $this->resourcesItemManager = $resourcesItemManager;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function addResourcesItemsFormData(ApplicationProcessEntity $applicationProcess, array &$formData): void {
    $resourcesItems = $this->resourcesItemManager->getByApplicationProcessId(
      $applicationProcess->getId()
    );

    foreach ($resourcesItems as $resourcesItem) {
      if ('' === $resourcesItem->getDataPointer()) {
        // Happens for resources items created before $resourcesItem keyword was added to JSON schema.
        continue;
      }

      $jsonPointer = JsonPointer::parse($resourcesItem->getDataPointer());
      if (NULL === $jsonPointer) {
        throw new \RuntimeException(sprintf('Invalid data pointer "%s"', $resourcesItem->getDataPointer()));
      }

      /** @var list<string> $path */
      $path = $jsonPointer->path();
      if ([] === $resourcesItem->getProperties()) {
        // @phpstan-ignore parameterByRef.type
        ArrayUtil::setValue($formData, $path, $resourcesItem->getAmount());
      }
      else {
        // @phpstan-ignore parameterByRef.type
        ArrayUtil::setValue($formData, $path, $resourcesItem->getProperties());
      }
    }
  }

}
