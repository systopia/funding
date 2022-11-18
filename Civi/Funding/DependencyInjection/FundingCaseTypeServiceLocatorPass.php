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

use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationJsonSchemaGetHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationJsonSchemaGetHandlerInterface;
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
   * @phpstan-var array<string>
   */
  private array $fundingCaseTypes = [];

  /**
   * @inheritDoc
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   */
  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
  public function process(ContainerBuilder $container): void {
  // phpcs:enable
    $applicationFormDataFactoryServices =
      $this->getTaggedServices($container, 'funding.application.form_data_factory');
    $applicationJsonSchemaFactoryServices =
      $this->getTaggedServices($container, 'funding.application.json_schema_factory');
    $applicationUiSchemaFactoryServices =
      $this->getTaggedServices($container, 'funding.application.ui_schema_factory');
    $applicationStatusDeterminerServices =
      $this->getTaggedServices($container, 'funding.application.status_determiner');

    $applicationFormNewCreateHandlerServices =
      $this->getTaggedServices($container, 'funding.application.form_new_create_handler');
    $applicationFormNewValidateHandlerServices =
      $this->getTaggedServices($container, 'funding.application.form_new_validate_handler');
    $applicationFormNewSubmitHandlerServices =
      $this->getTaggedServices($container, 'funding.application.form_new_submit_handler');

    $applicationFormCreateHandlerServices =
      $this->getTaggedServices($container, 'funding.application.form_create_handler');
    $applicationFormDataGetHandlerServices =
      $this->getTaggedServices($container, 'funding.application.form_data_get_handler');
    $applicationFormValidateHandlerServices =
      $this->getTaggedServices($container, 'funding.application.form_validate_handler');
    $applicationFormSubmitHandlerServices =
      $this->getTaggedServices($container, 'funding.application.form_submit_handler');
    $applicationFormJsonSchemaGetHandlerServices =
      $this->getTaggedServices($container, 'funding.application.json_schema_get_handler');

    $serviceLocatorServices =
      $this->getTaggedServices($container, 'funding.case.type.service_locator');

    foreach ($this->fundingCaseTypes as $fundingCaseType) {
      if (isset($serviceLocatorServices[$fundingCaseType])) {
        continue;
      }

      $applicationFormNewCreateHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormNewCreateHandler::class,
        [
          '$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType],
          '$uiSchemaFactory' => $applicationUiSchemaFactoryServices[$fundingCaseType],
        ]
      );

      $applicationFormNewValidateHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormNewValidateHandler::class,
        ['$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType]]
      );

      $applicationFormNewSubmitHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormNewSubmitHandler::class,
        [
          '$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType],
          '$statusDeterminer' => $applicationStatusDeterminerServices[$fundingCaseType],
        ]
      );

      $applicationFormJsonSchemaGetHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationJsonSchemaGetHandler::class,
        ['$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType]]
      );

      $applicationFormValidateHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormValidateHandler::class,
        ['$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType]]
      );

      $applicationFormDataGetHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormDataGetHandler::class,
        [
          '$formDataFactory' => $applicationFormDataFactoryServices[$fundingCaseType],
          '$validateHandler' => $applicationFormValidateHandlerServices[$fundingCaseType],
        ]
      );

      $applicationFormCreateHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormCreateHandler::class,
        [
          '$jsonSchemaGetHandler' => $applicationFormJsonSchemaGetHandlerServices[$fundingCaseType],
          '$uiSchemaFactory' => $applicationUiSchemaFactoryServices[$fundingCaseType],
          '$dataGetHandler' => $applicationFormDataGetHandlerServices[$fundingCaseType],
        ]
      );

      $applicationFormSubmitHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormSubmitHandler::class,
        [
          '$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType],
          '$statusDeterminer' => $applicationStatusDeterminerServices[$fundingCaseType],
        ]
      );

      $services = [
        ApplicationFormNewCreateHandlerInterface::class
        => $applicationFormNewCreateHandlerServices[$fundingCaseType],
        ApplicationFormNewValidateHandlerInterface::class
        => $applicationFormNewValidateHandlerServices[$fundingCaseType],
        ApplicationFormNewSubmitHandlerInterface::class
        => $applicationFormNewSubmitHandlerServices[$fundingCaseType],
        ApplicationFormCreateHandlerInterface::class
        => $applicationFormCreateHandlerServices[$fundingCaseType],
        ApplicationFormDataGetHandlerInterface::class => $applicationFormDataGetHandlerServices[$fundingCaseType],
        ApplicationFormValidateHandlerInterface::class
        => $applicationFormValidateHandlerServices[$fundingCaseType],
        ApplicationFormSubmitHandlerInterface::class => $applicationFormSubmitHandlerServices[$fundingCaseType],
        ApplicationJsonSchemaGetHandlerInterface::class
        => $applicationFormJsonSchemaGetHandlerServices[$fundingCaseType],
      ];

      $serviceLocatorServices[$fundingCaseType] = $this->createService(
        $container,
        $fundingCaseType,
        FundingCaseTypeServiceLocator::class,
        [ServiceLocatorTagPass::register($container, $services)]
      );
    }

    foreach (array_keys($applicationStatusDeterminerServices) as $fundingCaseType) {
      if (!isset($serviceLocatorServices[$fundingCaseType])) {
        throw new RuntimeException(sprintf('No form factory for funding case type "%s" defined', $fundingCaseType));
      }
    }

    $container->register(FundingCaseTypeServiceLocatorContainer::class, FundingCaseTypeServiceLocatorContainer::class)
      ->addArgument(ServiceLocatorTagPass::register($container, $serviceLocatorServices));
  }

  /**
   * @phpstan-param array<string|int, Reference> $arguments
   */
  private function createService(
    ContainerBuilder $container,
    string $fundingCaseType,
    string $class,
    array $arguments
  ): Reference {
    $serviceId = $class . ':' . $fundingCaseType;
    $container->autowire($serviceId, $class)->setArguments($arguments);

    return new Reference($serviceId);
  }

  /**
   * @phpstan-return array<string, Reference>
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   */
  private function getTaggedServices(ContainerBuilder $container, string $tagName): array {
    $services = [];
    foreach ($container->findTaggedServiceIds($tagName) as $id => $tags) {
      foreach ($tags as $attributes) {
        foreach ($this->getFundingCaseTypes($container, $id, $attributes) as $fundingCaseType) {
          if (isset($services[$fundingCaseType])) {
            throw new RuntimeException(
              sprintf('Duplicate service with tag "%s" and funding case type "%s"', $tagName, $fundingCaseType)
            );
          }
          $services[$fundingCaseType] = new Reference($id);
          if (!in_array($fundingCaseType, $this->fundingCaseTypes, TRUE)) {
            $this->fundingCaseTypes[] = $fundingCaseType;
          }
        }
      }
    }

    return $services;
  }

  /**
   * @phpstan-param array{funding_case_type?: string, funding_case_types?: array<string>} $attributes
   *
   * @phpstan-return array<string>
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
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
