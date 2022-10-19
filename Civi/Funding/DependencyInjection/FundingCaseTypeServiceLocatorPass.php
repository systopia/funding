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

namespace Civi\Funding\DependencyInjection;

use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\FundingCaseTypeServiceLocator;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @codeCoverageIgnore
 */
final class FundingCaseTypeServiceLocatorPass implements CompilerPassInterface {

  /**
   * @phpstan-var array<string, Reference>
   */
  private array $applicationFormFactoryServices = [];

  /**
   * @phpstan-var array<string, Reference>
   */
  private array $applicationStatusDeterminerServices = [];

  /**
   * @phpstan-var array<string, Reference>
   */
  private array $serviceLocatorServices = [];

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $this->handleApplicationFormFactories($container);
    $this->handleApplicationStatusDeterminers($container);
    $this->handleFundingCaseTypeServiceLocators($container);

    $defaultStatusDeterminerService = new Reference(ApplicationProcessStatusDeterminerInterface::class);
    $defaultFormValidatorService = new Reference(FormValidatorInterface::class);

    foreach ($this->applicationFormFactoryServices as $fundingCaseType => $formFactoryService) {
      if (isset($this->serviceLocatorServices[$fundingCaseType])) {
        continue;
      }

      $services = [
        ApplicationFormFactoryInterface::class => $formFactoryService,
        ApplicationProcessStatusDeterminerInterface::class =>
        $this->applicationStatusDeterminerServices[$fundingCaseType] ?? $defaultStatusDeterminerService,
        FormValidatorInterface::class => $defaultFormValidatorService,
      ];

      $serviceLocatorId = 'funding.case.type.service_locator.' . $fundingCaseType;
      $container->register($serviceLocatorId, FundingCaseTypeServiceLocator::class)
        ->addArgument(ServiceLocatorTagPass::register($container, $services));
      $this->serviceLocatorServices[$fundingCaseType] = new Reference($serviceLocatorId);
    }

    foreach (array_keys($this->applicationStatusDeterminerServices) as $fundingCaseType) {
      if (!isset($this->serviceLocatorServices[$fundingCaseType])) {
        throw new RuntimeException(sprintf('No form factory for funding case type "%s" defined', $fundingCaseType));
      }
    }

    $container->register(FundingCaseTypeServiceLocatorContainer::class, FundingCaseTypeServiceLocatorContainer::class)
      ->addArgument(ServiceLocatorTagPass::register($container, $this->serviceLocatorServices));
  }

  private function handleApplicationFormFactories(ContainerBuilder $container): void {
    foreach ($container->findTaggedServiceIds('funding.application.form_factory') as $id => $tags) {
      foreach ($tags as $attributes) {
        foreach ($this->getFundingCaseTypes($container, $id, $attributes) as $fundingCaseType) {
          if (isset($this->applicationFormFactoryServices[$fundingCaseType])) {
            throw new RuntimeException(
              sprintf('Duplicate application form factory definition for funding case type "%s"', $fundingCaseType)
            );
          }
          $this->applicationFormFactoryServices[$fundingCaseType] = new Reference($id);
        }
      }
    }
  }

  private function handleApplicationStatusDeterminers(ContainerBuilder $container): void {
    foreach ($container->findTaggedServiceIds('funding.application.status_determiner') as $id => $tags) {
      foreach ($tags as $attributes) {
        foreach ($this->getFundingCaseTypes($container, $id, $attributes) as $fundingCaseType) {
          if (isset($this->applicationStatusDeterminerServices[$fundingCaseType])) {
            throw new RuntimeException(
              sprintf('Duplicate application status determiner for funding case type "%s"', $fundingCaseType)
            );
          }
          $this->applicationStatusDeterminerServices[$fundingCaseType] = new Reference($id);
        }
      }
    }
  }

  private function handleFundingCaseTypeServiceLocators(ContainerBuilder $container): void {
    foreach ($container->findTaggedServiceIds('funding.case.type.service_locator') as $id => $tags) {
      foreach ($tags as $attributes) {
        foreach ($this->getFundingCaseTypes($container, $id, $attributes) as $fundingCaseType) {
          if (isset($this->serviceLocatorServices[$fundingCaseType])) {
            throw new RuntimeException(
              sprintf('Duplicate funding case type service locator for funding case type "%s"', $fundingCaseType)
            );
          }
          $this->serviceLocatorServices[$fundingCaseType] = new Reference($id);
        }
      }
    }
  }

  /**
   * @phpstan-param array{funding_case_type?: string, funding_case_types?: array<string>} $attributes
   *
   * @phpstan-return array<string>
   */
  private function getFundingCaseTypes(ContainerBuilder $container, string $id, array $attributes): array {
    if (array_key_exists('funding_case_types', $attributes)) {
      return $attributes['funding_case_types'];
    }

    if (array_key_exists('funding_case_type', $attributes)) {
      return [$attributes['funding_case_type']];
    }

    $class = $this->getServiceClass($container, $id);
    if (method_exists($class, 'getSupportedFundingCaseTypes')) {
      /** @phpstan-var array<string> $fundingCaseTypes */
      $fundingCaseTypes = $class::getSupportedFundingCaseTypes();

      return $fundingCaseTypes;
    }

    if (!method_exists($class, 'getSupportedFundingCaseType')) {
      throw new RuntimeException(sprintf('No funding case type specified for service "%s"', $id));
    }

    /** @var string $fundingCaseType */
    $fundingCaseType = $class::getSupportedFundingCaseType();

    return [$fundingCaseType];
  }

  /**
   * @phpstan-return class-string
   */
  private function getServiceClass(ContainerBuilder $container, string $id): string {
    $definition = $container->getDefinition($id);

    /** @phpstan-var class-string $class */
    $class = $definition->getClass() ?? $id;

    return $class;
  }

}
