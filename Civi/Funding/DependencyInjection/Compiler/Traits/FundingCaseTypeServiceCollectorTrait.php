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

use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

trait FundingCaseTypeServiceCollectorTrait {

  use TaggedFundingCaseTypeServicesTrait;

  /**
   * @phpstan-param class-string $collectorClass
   * @phpstan-param class-string $interfaceClass
   * @param int|string $argumentKey
   */
  protected function registerCollector(
    ContainerBuilder $container,
    string $collectorClass,
    string $interfaceClass,
    $argumentKey = 0
  ): void {
    $services = $this->getTaggedFundingCaseTypeServices($container, $interfaceClass::SERVICE_TAG);
    $container->autowire($interfaceClass, $collectorClass)
      ->setArgument($argumentKey, ServiceLocatorTagPass::register($container, $services));
  }

}
