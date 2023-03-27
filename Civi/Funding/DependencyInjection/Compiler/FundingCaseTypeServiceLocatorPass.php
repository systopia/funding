<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsAddIdentifiersHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCommentPersistHandler;
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
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsAddIdentifiersHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\Decorator\ApplicationFormNewSubmitEventDecorator;
use Civi\Funding\ApplicationProcess\Handler\Decorator\ApplicationFormSubmitEventDecorator;
use Civi\Funding\FundingCase\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\FundingCaseStatusDeterminer;
use Civi\Funding\FundingCase\FundingCaseStatusDeterminerInterface;
use Civi\Funding\FundingCase\Handler\Decorator\FundingCaseApproveEventDecorator;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandler;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandler;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface;
use Civi\Funding\FundingCaseTypeServiceLocator;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Civi\Funding\TransferContract\Handler\TransferContractRenderHandler;
use Civi\Funding\TransferContract\Handler\TransferContractRenderHandlerInterface;
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
  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.CyclomaticComplexity.MaxExceeded
  public function process(ContainerBuilder $container): void {
  // phpcs:enable
    $applicationActionStatusInfoServices =
      $this->getTaggedServices($container, 'funding.application.action_status_info');

    $applicationFormDataFactoryServices =
      $this->getTaggedServices($container, 'funding.application.form_data_factory');
    $applicationJsonSchemaFactoryServices =
      $this->getTaggedServices($container, 'funding.application.json_schema_factory');
    $applicationUiSchemaFactoryServices =
      $this->getTaggedServices($container, 'funding.application.ui_schema_factory');
    $applicationActionsDeterminerServices =
      $this->getTaggedServices($container, 'funding.application.actions_determiner');
    $applicationStatusDeterminerServices =
      $this->getTaggedServices($container, 'funding.application.status_determiner');
    $applicationCostItemsFactoryServices =
      $this->getTaggedServices($container, 'funding.application.cost_items_factory');
    $applicationResourcesItemsFactoryServices =
      $this->getTaggedServices($container, 'funding.application.resources_items_factory');

    $fundingCaseActionsDeterminerServices =
      $this->getTaggedServices($container, FundingCaseActionsDeterminerInterface::TAG);
    $fundingCaseStatusDeterminerServices =
      $this->getTaggedServices($container, 'funding.case.status_determiner');

    $applicationDeleteHandlerServices =
      $this->getTaggedServices($container, 'funding.application.delete_handler');

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

    $applicationFormCommentPersistHandlerServices =
      $this->getTaggedServices($container, 'funding.application.form_comment_persist_handler');
    $applicationFormJsonSchemaGetHandlerServices =
      $this->getTaggedServices($container, 'funding.application.json_schema_get_handler');

    $applicationCostItemsAddIdentifiersHandlerServices =
      $this->getTaggedServices($container, 'funding.application.cost_items_add_identifiers_handler');
    $applicationCostItemsPersistHandlerServices =
      $this->getTaggedServices($container, 'funding.application.cost_items_persist_handler');

    $applicationResourcesItemsAddIdentifiersHandlerServices =
      $this->getTaggedServices($container, 'funding.application.resources_items_add_identifiers_handler');
    $applicationResourcesItemsPersistHandlerServices =
      $this->getTaggedServices($container, 'funding.application.resources_items_persist_handler');

    $fundingCaseApproveHandlerServices = $this->getTaggedServices($container, FundingCaseApproveHandlerInterface::TAG);
    $fundingCasePossibleActionsGetHandlerServices =
      $this->getTaggedServices($container, FundingCasePossibleActionsGetHandlerInterface::TAG);
    $transferContractRecreateHandlerServices =
      $this->getTaggedServices($container, TransferContractRecreateHandlerInterface::TAG);

    $transferContractRenderHandlerServices =
      $this->getTaggedServices($container, TransferContractRenderHandlerInterface::TAG);

    $serviceLocatorServices =
      $this->getTaggedServices($container, 'funding.case.type.service_locator');

    foreach ($this->fundingCaseTypes as $fundingCaseType) {
      if (!isset($applicationActionStatusInfoServices[$fundingCaseType])) {
        throw new RuntimeException(
          sprintf('Application action status info for funding case type "%s" missing', $fundingCaseType)
        );
      }

      if (isset($serviceLocatorServices[$fundingCaseType])) {
        continue;
      }

      $fundingCaseStatusDeterminerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        FundingCaseStatusDeterminer::class,
        [
          '$info' => $applicationActionStatusInfoServices[$fundingCaseType],
        ]
      );

      $applicationDeleteHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationDeleteHandler::class,
        [
          '$actionsDeterminer' => $applicationActionsDeterminerServices[$fundingCaseType],
        ]
      );

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
        ],
        [ApplicationFormNewSubmitEventDecorator::class => []],
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

      $applicationFormCommentPersistHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormCommentPersistHandler::class,
        []
      );

      $applicationFormSubmitHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFormSubmitHandler::class,
        [
          '$commentPersistHandler' => $applicationFormCommentPersistHandlerServices[$fundingCaseType],
          '$info' => $applicationActionStatusInfoServices[$fundingCaseType],
          '$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType],
          '$statusDeterminer' => $applicationStatusDeterminerServices[$fundingCaseType],
        ],
        [ApplicationFormSubmitEventDecorator::class => []],
      );

      $applicationCostItemsAddIdentifiersHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationCostItemsAddIdentifiersHandler::class,
        ['$costItemsFactory' => $applicationCostItemsFactoryServices[$fundingCaseType]]
      );

      $applicationCostItemsPersistHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationCostItemsPersistHandler::class,
        ['$costItemsFactory' => $applicationCostItemsFactoryServices[$fundingCaseType]]
      );

      $applicationResourcesItemsAddIdentifiersHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationResourcesItemsAddIdentifiersHandler::class,
        ['$resourcesItemsFactory' => $applicationResourcesItemsFactoryServices[$fundingCaseType]]
      );

      $applicationResourcesItemsPersistHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationResourcesItemsPersistHandler::class,
        ['$resourcesItemsFactory' => $applicationResourcesItemsFactoryServices[$fundingCaseType]]
      );

      $applicationSnapshotCreateHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationSnapshotCreateHandler::class,
        []
      );

      $fundingCaseApproveHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        FundingCaseApproveHandler::class,
        [
          '$actionsDeterminer' => $fundingCaseActionsDeterminerServices[$fundingCaseType],
          '$statusDeterminer' => $fundingCaseStatusDeterminerServices[$fundingCaseType],
        ],
        [FundingCaseApproveEventDecorator::class => []],
      );

      $fundingCasePossibleActionsGetHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        FundingCasePossibleActionsGetHandler::class,
        ['$actionsDeterminer' => $fundingCaseActionsDeterminerServices[$fundingCaseType]],
      );

      $transferContractRecreateHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        TransferContractRecreateHandler::class,
        ['$actionsDeterminer' => $fundingCaseActionsDeterminerServices[$fundingCaseType]]
      );

      $transferContractRenderHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        TransferContractRenderHandler::class,
        [],
      );

      $services = [
        ApplicationDeleteHandlerInterface::class => $applicationDeleteHandlerServices[$fundingCaseType],
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
        ApplicationCostItemsAddIdentifiersHandlerInterface::class
        => $applicationCostItemsAddIdentifiersHandlerServices[$fundingCaseType],
        ApplicationCostItemsPersistHandlerInterface::class
        => $applicationCostItemsPersistHandlerServices[$fundingCaseType],
        ApplicationResourcesItemsAddIdentifiersHandlerInterface::class
        => $applicationResourcesItemsAddIdentifiersHandlerServices[$fundingCaseType],
        ApplicationResourcesItemsPersistHandlerInterface::class
        => $applicationResourcesItemsPersistHandlerServices[$fundingCaseType],
        ApplicationSnapshotCreateHandlerInterface::class => $applicationSnapshotCreateHandlerServices[$fundingCaseType],
        FundingCaseApproveHandlerInterface::class => $fundingCaseApproveHandlerServices[$fundingCaseType],
        FundingCaseStatusDeterminerInterface::class => $fundingCaseStatusDeterminerServices[$fundingCaseType],
        FundingCasePossibleActionsGetHandlerInterface::class
        => $fundingCasePossibleActionsGetHandlerServices[$fundingCaseType],
        TransferContractRecreateHandlerInterface::class => $transferContractRecreateHandlerServices[$fundingCaseType],
        TransferContractRenderHandlerInterface::class => $transferContractRenderHandlerServices[$fundingCaseType],
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

    $container->register(
      ApplicationProcessActionStatusInfoContainer::class,
      ApplicationProcessActionStatusInfoContainer::class
    )->addArgument(ServiceLocatorTagPass::register($container, $applicationActionStatusInfoServices));

    $container->register(FundingCaseTypeServiceLocatorContainer::class, FundingCaseTypeServiceLocatorContainer::class)
      ->addArgument(ServiceLocatorTagPass::register($container, $serviceLocatorServices));
  }

  /**
   * @phpstan-param array<string|int, Reference> $arguments
   * @phpstan-param array<string, array<string|int, Reference>> $decorators
   *   Class names mapped to arguments. The handler to decorate has to be the
   *   first argument in the decorator class constructor.
   */
  private function createService(
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
    $container->autowire($serviceId, $class)->setArguments($arguments);

    foreach ($decorators as $decoratorClass => $decoratorArguments) {
      $decoratorServiceId = $decoratorClass . ':' . $fundingCaseType;
      array_unshift($decoratorArguments, new Reference($serviceId));
      $container->autowire($decoratorServiceId, $decoratorClass)->setArguments($decoratorArguments);
      $serviceId = $decoratorServiceId;
    }

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
