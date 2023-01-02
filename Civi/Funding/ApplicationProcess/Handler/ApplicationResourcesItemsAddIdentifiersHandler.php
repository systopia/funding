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

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemsFactoryInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationResourcesItemsAddIdentifiersCommand;

// phpcs:disable: Generic.Files.LineLength.TooLong
final class ApplicationResourcesItemsAddIdentifiersHandler implements ApplicationResourcesItemsAddIdentifiersHandlerInterface {
// phpcs:enable

  private ApplicationResourcesItemsFactoryInterface $resourcesItemsFactory;

  public function __construct(ApplicationResourcesItemsFactoryInterface $resourcesItemsFactory) {
    $this->resourcesItemsFactory = $resourcesItemsFactory;
  }

  /**
   * @inheritDoc
   */
  public function handle(ApplicationResourcesItemsAddIdentifiersCommand $command): array {
    return $this->resourcesItemsFactory->addIdentifiers($command->getRequestData());
  }

}
