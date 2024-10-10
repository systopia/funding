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

namespace Civi\Funding\DependencyInjection\Compiler\Traits;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

trait CreateFundingCaseTypeServiceTrait {

  /**
   * @phpstan-param array<string|int, Reference> $arguments
   * @phpstan-param array<string, array<string|int, Reference>> $decorators
   *   Class names mapped to arguments. The handler to decorate has to be the
   *   first argument in the decorator class constructor.
   */
  private function createFundingCaseTypeService(
    ContainerBuilder $container,
    string $fundingCaseType,
    string $class,
    array $arguments,
    array $decorators = []
  ): Reference {
    $serviceId = $class;
    if ([] !== $arguments) {
      $serviceId .= ':' . $fundingCaseType;
    }

    $definition = $container->autowire($serviceId, $class)->setArguments($arguments);
    if (defined("$class::SERVICE_TAG")) {
      $definition->addTag($class::SERVICE_TAG, ['funding_case_type' => $fundingCaseType]);
    }

    foreach ($decorators as $decoratorClass => $decoratorArguments) {
      $decoratorServiceId = $decoratorClass . ':' . $fundingCaseType;
      array_unshift($decoratorArguments, new Reference($serviceId));
      $container->autowire($decoratorServiceId, $decoratorClass)->setArguments($decoratorArguments);
      $serviceId = $decoratorServiceId;
    }

    return new Reference($serviceId);
  }

}
