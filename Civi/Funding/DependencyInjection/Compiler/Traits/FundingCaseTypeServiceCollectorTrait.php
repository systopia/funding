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
   * @param class-string $collectorClass
   * @param class-string $interfaceClass
   *
   * @return array<string, \Symfony\Component\DependencyInjection\Reference>
   *   The services in the collector.
   */
  protected function registerCollector(
    ContainerBuilder $container,
    string $collectorClass,
    string $interfaceClass,
    int|string $argumentKey = 0,
    bool $public = FALSE,
  ): array {
    $services = $this->getTaggedFundingCaseTypeServices($container, $interfaceClass::SERVICE_TAG);
    $container->autowire($interfaceClass, $collectorClass)
      ->setArgument($argumentKey, ServiceLocatorTagPass::register($container, $services))
      ->setPublic($public);

    return $services;
  }

}
