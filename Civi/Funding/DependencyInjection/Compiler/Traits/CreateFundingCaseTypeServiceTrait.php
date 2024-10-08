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
    if ([] !== $arguments || [] !== $decorators) {
      // Create funding case type specific service.
      $serviceId .= ':' . $fundingCaseType;
      if ($container->hasDefinition($serviceId)) {
        throw new \RuntimeException(
          sprintf('A service with class "%s" for funding case type "%s" already exists', $class, $fundingCaseType)
        );
      }

      $definition = $container->autowire($serviceId, $class)->setArguments($arguments);

      foreach ($decorators as $decoratorClass => $decoratorArguments) {
        $decoratorServiceId = $decoratorClass . ':' . $fundingCaseType;
        array_unshift($decoratorArguments, new Reference($serviceId));
        $container->autowire($decoratorServiceId, $decoratorClass)->setArguments($decoratorArguments);
        $serviceId = $decoratorServiceId;
      }
    }
    else {
      // Use existing definition, if any, so previous tags aren't lost.
      $definition = $container->hasDefinition($serviceId)
        ? $container->findDefinition($serviceId)
        : $container->autowire($serviceId, $class);
    }

    $serviceTag = defined("$class::SERVICE_TAG") ? $class::SERVICE_TAG : NULL;
    if (NULL !== $serviceTag) {
      $definition->addTag($serviceTag, ['funding_case_type' => $fundingCaseType]);
    }

    return new Reference($serviceId);
  }

}
