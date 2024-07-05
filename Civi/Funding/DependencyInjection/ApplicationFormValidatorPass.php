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

namespace Civi\Funding\DependencyInjection;

use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormAddValidatorCollector;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormAddValidatorInterface;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormNewValidatorCollector;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormNewValidatorInterface;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidatorCollector;
use Civi\Funding\ApplicationProcess\Form\Validation\ApplicationFormValidatorInterface;
use Civi\Funding\DependencyInjection\Compiler\Traits\TaggedFundingCaseTypeServicesTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @codeCoverageIgnore
 */
final class ApplicationFormValidatorPass implements CompilerPassInterface {

  use TaggedFundingCaseTypeServicesTrait;

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $this->registerCollector(
      $container,
      ApplicationFormValidatorCollector::class,
      ApplicationFormValidatorInterface::class
    );

    $this->registerCollector(
      $container,
      ApplicationFormAddValidatorCollector::class,
      ApplicationFormAddValidatorInterface::class
    );

    $this->registerCollector(
      $container,
      ApplicationFormNewValidatorCollector::class,
      ApplicationFormNewValidatorInterface::class
    );
  }

  /**
   * @param class-string $collectorClass
   * @param class-string $interfaceClass
   */
  private function registerCollector(
    ContainerBuilder $container,
    string $collectorClass,
    string $interfaceClass
  ): void {
    $services = $this->getTaggedFundingCaseTypeServices($container, $interfaceClass::SERVICE_TAG);
    $container->register($interfaceClass, $collectorClass)
      ->addArgument(ServiceLocatorTagPass::register($container, $services));
  }

}
