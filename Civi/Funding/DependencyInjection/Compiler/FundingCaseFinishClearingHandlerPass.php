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
use Civi\Funding\FundingCase\Handler\FundingCaseFinishClearingHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseFinishClearingHandlerCollector;
use Civi\Funding\FundingCase\Handler\FundingCaseFinishClearingHandlerInterface;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Webmozart\Assert\Assert;

/**
 * @codeCoverageIgnore
 */
final class FundingCaseFinishClearingHandlerPass implements CompilerPassInterface {

  use CreateFundingCaseTypeServiceTrait;
  use TaggedFundingCaseTypeServicesTrait;

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $handlerServices = $this->getTaggedFundingCaseTypeServices(
      $container,
      FundingCaseFinishClearingHandlerInterface::SERVICE_TAG
    );
    $actionsDeterminerServices = $this->getTaggedFundingCaseTypeServices(
      $container,
      FundingCaseActionsDeterminerInterface::SERVICE_TAG
    );
    $statusDeterminerServices = $this->getTaggedFundingCaseTypeServices(
      $container,
      FundingCaseStatusDeterminerInterface::SERVICE_TAG
    );

    foreach (FundingCaseTypeMetaDataPass::$fundingCaseTypes as $fundingCaseType) {
      if (!isset($handlerServices[$fundingCaseType])) {
        Assert::keyExists($actionsDeterminerServices, $fundingCaseType);
        Assert::keyExists($statusDeterminerServices, $fundingCaseType);

        $handlerServices[$fundingCaseType] = $this->createFundingCaseTypeService(
          $container,
          $fundingCaseType,
          FundingCaseFinishClearingHandler::class,
          [
            '$actionsDeterminer' => $actionsDeterminerServices[$fundingCaseType],
            '$statusDeterminer' => $statusDeterminerServices[$fundingCaseType],
          ]
        );
      }
    }

    $container->register(
      FundingCaseFinishClearingHandlerInterface::class,
      FundingCaseFinishClearingHandlerCollector::class
    )->addArgument(ServiceLocatorTagPass::register($container, $handlerServices));
  }

}
