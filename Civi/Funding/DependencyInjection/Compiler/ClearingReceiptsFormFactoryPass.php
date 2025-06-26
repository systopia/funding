<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

use Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorCollector;
use Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorPermissionDecorator;
use Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorInterface;
use Civi\Funding\DependencyInjection\Compiler\Traits\TaggedFundingCaseTypeServicesTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ClearingReceiptsFormFactoryPass implements CompilerPassInterface {

  use TaggedFundingCaseTypeServicesTrait;

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $services = $this->getTaggedFundingCaseTypeServices($container, ReceiptsFormGeneratorInterface::class);
    foreach ($services as $fundingCaseType => $service) {
      $decoratorServiceId = ReceiptsFormGeneratorPermissionDecorator::class . ':' . $fundingCaseType;
      $container->autowire($decoratorServiceId, ReceiptsFormGeneratorPermissionDecorator::class)->addArgument($service);
      $services[$fundingCaseType] = new Reference($decoratorServiceId);
    }

    $container->register(ReceiptsFormGeneratorInterface::class, ReceiptsFormGeneratorCollector::class)
      ->addArgument(ServiceLocatorTagPass::register($container, $services));
  }

}
