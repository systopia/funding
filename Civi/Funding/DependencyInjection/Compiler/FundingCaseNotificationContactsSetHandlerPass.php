<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\DependencyInjection\Compiler;

use Civi\Funding\DependencyInjection\Compiler\Traits\CreateFundingCaseTypeServiceTrait;
use Civi\Funding\DependencyInjection\Compiler\Traits\TaggedFundingCaseTypeServicesTrait;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseNotificationContactsSetHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseNotificationContactsSetHandlerCollector;
use Civi\Funding\FundingCase\Handler\FundingCaseNotificationContactsSetHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @codeCoverageIgnore
 */
final class FundingCaseNotificationContactsSetHandlerPass implements CompilerPassInterface {

  use CreateFundingCaseTypeServiceTrait;
  use TaggedFundingCaseTypeServicesTrait;

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $actionsDeterminerServices = $this->getTaggedFundingCaseTypeServices(
      $container,
      FundingCaseActionsDeterminerInterface::SERVICE_TAG
    );
    $handlerServices = $this->getTaggedFundingCaseTypeServices(
      $container,
      FundingCaseNotificationContactsSetHandlerInterface::SERVICE_TAG
    );

    foreach (array_diff(
      FundingCaseTypeMetaDataPass::$fundingCaseTypes,
      array_keys($handlerServices)
    ) as $fundingCaseType) {
      $handlerServices[$fundingCaseType] = $this->createFundingCaseTypeService(
        $container,
        $fundingCaseType,
        FundingCaseNotificationContactsSetHandler::class,
        ['$actionsDeterminer' => $actionsDeterminerServices[$fundingCaseType]]
      );
    }

    $container->register(
      FundingCaseNotificationContactsSetHandlerInterface::class,
      FundingCaseNotificationContactsSetHandlerCollector::class
    )->addArgument(ServiceLocatorTagPass::register($container, $handlerServices));
  }

}
