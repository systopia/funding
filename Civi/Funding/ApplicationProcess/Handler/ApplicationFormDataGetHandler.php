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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormDataGetCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\Form\Application\ApplicationCostItemsFormDataLoaderInterface;
use Civi\Funding\Form\Application\ApplicationFormDataFactoryInterface;
use Civi\Funding\Form\Application\ApplicationResourcesItemsFormDataLoaderInterface;

final class ApplicationFormDataGetHandler implements ApplicationFormDataGetHandlerInterface {

  private ApplicationCostItemsFormDataLoaderInterface $costItemsFormDataLoader;

  private ApplicationFormDataFactoryInterface $formDataFactory;

  private ApplicationResourcesItemsFormDataLoaderInterface $resourcesItemsFormDataLoader;

  private ApplicationFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ApplicationCostItemsFormDataLoaderInterface $costItemsFormDataLoader,
    ApplicationFormDataFactoryInterface $formDataFactory,
    ApplicationResourcesItemsFormDataLoaderInterface $resourcesItemsFormDataLoader,
    ApplicationFormValidateHandlerInterface $validateHandler
  ) {
    $this->costItemsFormDataLoader = $costItemsFormDataLoader;
    $this->formDataFactory = $formDataFactory;
    $this->resourcesItemsFormDataLoader = $resourcesItemsFormDataLoader;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @inheritDoc
   */
  public function handle(ApplicationFormDataGetCommand $command): array {
    if ($command->hasFlag(ApplicationFormDataGetCommand::FLAG_COPY)) {
      $data = $this->formDataFactory->createFormDataForCopy(
        $command->getApplicationProcess(),
        $command->getFundingCase(),
      );
    }
    else {
      $data = $this->formDataFactory->createFormData(
        $command->getApplicationProcess(),
        $command->getFundingCase(),
      );
    }

    $this->costItemsFormDataLoader->addCostItemsFormData($command->getApplicationProcess(), $data);
    $this->resourcesItemsFormDataLoader->addResourcesItemsFormData($command->getApplicationProcess(), $data);

    // Perform calculations
    $result = $this->validateHandler->handle(new ApplicationFormValidateCommand(
      $command->getApplicationProcessBundle(),
      $command->getApplicationProcessStatusList(),
      $data,
      10
    ));

    return $result->getValidatedData()->getRawData();
  }

}
