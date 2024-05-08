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

use Civi\Funding\ApplicationProcess\ActionsContainer\ApplicationSubmitActionsContainerInterface;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoContainer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ApplicationProcessActionStatusInfoInterface;
use Civi\Funding\ApplicationProcess\ApplicationFormFilesFactoryInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationAllowedActionsGetHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationAllowedActionsGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesAddIdentifiersHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCommentPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCommentPersistHandlerInterface;
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
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\Decorator\ApplicationFormAddSubmitEventDecorator;
use Civi\Funding\ApplicationProcess\Handler\Decorator\ApplicationFormNewSubmitEventDecorator;
use Civi\Funding\ApplicationProcess\Handler\Decorator\ApplicationFormSubmitEventDecorator;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\DependencyInjection\Compiler\Traits\TaggedFundingCaseTypeServicesTrait;
use Civi\Funding\Form\Application\ApplicationFormDataFactoryInterface;
use Civi\Funding\Form\Application\ApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactory;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\Form\Application\ApplicationUiSchemaFactoryInterface;
use Civi\Funding\Form\Application\ApplicationValidatorInterface;
use Civi\Funding\Form\Application\CombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\NonCombinedApplicationJsonSchemaFactoryInterface;
use Civi\Funding\Form\Application\NonCombinedApplicationValidatorInterface;
use Civi\Funding\Form\FundingCase\FundingCaseFormDataFactoryInterface;
use Civi\Funding\Form\FundingCase\FundingCaseJsonSchemaFactoryInterface;
use Civi\Funding\Form\FundingCase\FundingCaseUiSchemaFactoryInterface;
use Civi\Funding\Form\FundingCase\FundingCaseValidatorInterface;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Handler\Decorator\FundingCaseApproveEventDecorator;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormDataGetHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseFormDataGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewGetHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewSubmitHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewSubmitHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewValidateHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewValidateHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateGetHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateSubmitHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateSubmitHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandler;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\FundingCase\Handler\Helper\ApplicationAllowedActionApplier;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandler;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface;
use Civi\Funding\FundingCase\StatusDeterminer\CombinedFundingCaseStatusDeterminer;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Civi\Funding\FundingCase\StatusDeterminer\NonCombinedFundingCaseStatusDeterminer;
use Civi\Funding\FundingCaseTypeServiceLocator;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Civi\Funding\FundingCaseTypeServiceLocatorInterface;
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

  use TaggedFundingCaseTypeServicesTrait;

  /**
   * @inheritDoc
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   */
  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh, Generic.Metrics.CyclomaticComplexity.MaxExceeded
  public function process(ContainerBuilder $container): void {
  // phpcs:enable
    $applicationActionsContainerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationSubmitActionsContainerInterface::SERVICE_TAG);
    $applicationSubmitActionsFactoryServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationSubmitActionsFactoryInterface::SERVICE_TAG);
    $applicationActionStatusInfoServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationProcessActionStatusInfoInterface::SERVICE_TAG);

    $applicationFormDataFactoryServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormDataFactoryInterface::SERVICE_TAG);
    $applicationJsonSchemaFactoryServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationJsonSchemaFactoryInterface::SERVICE_TAG);
    $applicationUiSchemaFactoryServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationUiSchemaFactoryInterface::SERVICE_TAG);
    $applicationValidator =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationValidatorInterface::SERVICE_TAG);
    $applicationActionsDeterminerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationProcessActionsDeterminerInterface::SERVICE_TAG);
    $applicationStatusDeterminerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationProcessStatusDeterminerInterface::SERVICE_TAG);
    $applicationFormFilesFactoryServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormFilesFactoryInterface::SERVICE_TAG);

    $applicationAllowedActionsGetHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationAllowedActionsGetHandlerInterface::SERVICE_TAG);

    $applicationDeleteHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationDeleteHandlerInterface::SERVICE_TAG);

    $applicationFormNewCreateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormNewCreateHandlerInterface::SERVICE_TAG);
    $applicationFormNewValidateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormValidateHandlerInterface::SERVICE_TAG);
    $applicationFormNewSubmitHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormNewSubmitHandlerInterface::SERVICE_TAG);

    $applicationFormAddCreateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormAddCreateHandlerInterface::SERVICE_TAG);
    $applicationFormAddValidateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormAddValidateHandlerInterface::SERVICE_TAG);
    $applicationFormAddSubmitHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormAddSubmitHandlerInterface::SERVICE_TAG);

    $applicationFormCreateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormCreateHandlerInterface::SERVICE_TAG);
    $applicationFormDataGetHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormDataGetHandlerInterface::SERVICE_TAG);
    $applicationFormValidateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormValidateHandlerInterface::SERVICE_TAG);
    $applicationFormSubmitHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormSubmitHandlerInterface::SERVICE_TAG);

    $applicationActionApplyHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationActionApplyHandlerInterface::SERVICE_TAG);

    $applicationFormCommentPersistHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFormCommentPersistHandlerInterface::SERVICE_TAG);
    $applicationFormJsonSchemaGetHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationJsonSchemaGetHandlerInterface::SERVICE_TAG);

    $applicationCostItemsPersistHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationCostItemsPersistHandlerInterface::SERVICE_TAG);

    $applicationResourcesItemsPersistHandlerServices = $this->getTaggedFundingCaseTypeServices(
      $container,
      ApplicationResourcesItemsPersistHandlerInterface::SERVICE_TAG
    );

    $applicationFilesAddIdentifiersHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFilesAddIdentifiersHandlerInterface::SERVICE_TAG);
    $applicationFilesPersistHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationFilesPersistHandlerInterface::SERVICE_TAG);

    $applicationSnapshotCreateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, ApplicationSnapshotCreateHandlerInterface::SERVICE_TAG);

    $fundingCaseActionsDeterminerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseActionsDeterminerInterface::SERVICE_TAG);
    $fundingCaseStatusDeterminerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseStatusDeterminerInterface::SERVICE_TAG);

    $fundingCaseFormDataFactoryServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseFormDataFactoryInterface::SERVICE_TAG);
    $fundingCaseJsonSchemaFactoryServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseJsonSchemaFactoryInterface::SERVICE_TAG);
    $fundingCaseUiSchemaFactoryServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseUiSchemaFactoryInterface::SERVICE_TAG);
    $fundingCaseValidatorServices
      = $this->getTaggedFundingCaseTypeServices($container, FundingCaseValidatorInterface::SERVICE_TAG);

    $fundingCaseFormNewGetHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseFormNewGetHandlerInterface::SERVICE_TAG);
    $fundingCaseFormNewSubmitHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseFormNewSubmitHandlerInterface::SERVICE_TAG);
    $fundingCaseFormNewValidateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseFormNewValidateHandlerInterface::SERVICE_TAG);

    $fundingCaseFormUpdateGetHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseFormUpdateGetHandlerInterface::SERVICE_TAG);
    $fundingCaseFormDataGetHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseFormDataGetHandlerInterface::SERVICE_TAG);
    $fundingCaseFormUpdateSubmitHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseFormUpdateSubmitHandlerInterface::SERVICE_TAG);
    $fundingCaseFormUpdateValidateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseFormUpdateValidateHandlerInterface::SERVICE_TAG);

    $fundingCaseApproveHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseApproveHandlerInterface::SERVICE_TAG);
    $fundingCasePossibleActionsGetHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCasePossibleActionsGetHandlerInterface::SERVICE_TAG);
    $fundingCaseUpdateAmountApprovedHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseUpdateAmountApprovedHandlerInterface::SERVICE_TAG);

    $transferContractRecreateHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, TransferContractRecreateHandlerInterface::SERVICE_TAG);
    $transferContractRenderHandlerServices =
      $this->getTaggedFundingCaseTypeServices($container, TransferContractRenderHandlerInterface::SERVICE_TAG);

    $serviceLocatorServices =
      $this->getTaggedFundingCaseTypeServices($container, FundingCaseTypeServiceLocatorInterface::SERVICE_TAG);

    foreach ($this->fundingCaseTypes as $fundingCaseType) {
      if (!isset($applicationActionStatusInfoServices[$fundingCaseType])) {
        throw new RuntimeException(
          sprintf('Application action status info for funding case type "%s" missing', $fundingCaseType)
        );
      }

      if (isset($serviceLocatorServices[$fundingCaseType])) {
        continue;
      }

      $applicationAllowedActionsGetHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationAllowedActionsGetHandler::class,
        [
          '$submitActionsFactory' => $applicationSubmitActionsFactoryServices[$fundingCaseType] ??=
          $this->createService(
            $container,
            $fundingCaseType,
            ApplicationSubmitActionsFactory::class,
            [
              '$actionsDeterminer' => $applicationActionsDeterminerServices[$fundingCaseType],
              '$submitActionsContainer' => $applicationActionsContainerServices[$fundingCaseType],
            ],
          ),
        ],
      );

      $applicationDeleteHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationDeleteHandler::class,
        [
          '$actionsDeterminer' => $applicationActionsDeterminerServices[$fundingCaseType],
        ]
      );

      if ($this->isServiceReferenceInstanceOf(
        $container,
        $applicationJsonSchemaFactoryServices[$fundingCaseType] ?? NULL,
        NonCombinedApplicationJsonSchemaFactoryInterface::class
      ) || $this->isServiceReferenceInstanceOf(
        $container,
          $applicationValidator[$fundingCaseType] ?? NULL,
        NonCombinedApplicationValidatorInterface::class
      )) {
        $fundingCaseStatusDeterminerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          NonCombinedFundingCaseStatusDeterminer::class,
          [
            '$info' => $applicationActionStatusInfoServices[$fundingCaseType],
          ]
        );

        $applicationFormNewCreateHandlerServices[$fundingCaseType] ??= $this->createService(
          $container, $fundingCaseType, ApplicationFormNewCreateHandler::class, [
            '$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType],
            '$uiSchemaFactory' => $applicationUiSchemaFactoryServices[$fundingCaseType],
          ]
        );

        $applicationFormNewValidateHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          ApplicationFormNewValidateHandler::class,
          ['$validator' => $applicationValidator[$fundingCaseType]]
        );

        $applicationFormNewSubmitHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          ApplicationFormNewSubmitHandler::class,
          [
            '$statusDeterminer' => $applicationStatusDeterminerServices[$fundingCaseType],
            '$validator' => $applicationValidator[$fundingCaseType],
          ],
          [ApplicationFormNewSubmitEventDecorator::class => []],
        );
      }

      if ($this->isServiceReferenceInstanceOf(
          $container,
          $applicationJsonSchemaFactoryServices[$fundingCaseType] ?? NULL,
          CombinedApplicationJsonSchemaFactoryInterface::class
      ) || isset($fundingCaseJsonSchemaFactoryServices[$fundingCaseType])
      ) {
        $fundingCaseStatusDeterminerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          CombinedFundingCaseStatusDeterminer::class,
          [],
        );

        $applicationFormAddCreateHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          ApplicationFormAddCreateHandler::class,
          [
            '$jsonSchemaFactory' => $applicationJsonSchemaFactoryServices[$fundingCaseType],
            '$uiSchemaFactory' => $applicationUiSchemaFactoryServices[$fundingCaseType],
          ],
        );

        $applicationFormAddValidateHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          ApplicationFormAddValidateHandler::class,
          ['$validator' => $applicationValidator[$fundingCaseType]],
        );

        $applicationFormAddSubmitHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          ApplicationFormAddSubmitHandler::class,
          [
            '$statusDeterminer' => $applicationStatusDeterminerServices[$fundingCaseType],
          ],
          [ApplicationFormAddSubmitEventDecorator::class => []],
        );

        $fundingCaseFormNewGetHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          FundingCaseFormNewGetHandler::class,
          [
            '$jsonSchemaFactory' => $fundingCaseJsonSchemaFactoryServices[$fundingCaseType],
            '$uiSchemaFactory' => $fundingCaseUiSchemaFactoryServices[$fundingCaseType],
          ],
        );

        $fundingCaseFormNewSubmitHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          FundingCaseFormNewSubmitHandler::class,
          [],
        );

        $fundingCaseFormNewValidateHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          FundingCaseFormNewValidateHandler::class,
          [
            '$validator' => $fundingCaseValidatorServices[$fundingCaseType],
          ],
        );

        $fundingCaseFormUpdateGetHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          FundingCaseFormUpdateGetHandler::class,
          [
            '$jsonSchemaFactory' => $fundingCaseJsonSchemaFactoryServices[$fundingCaseType],
            '$uiSchemaFactory' => $fundingCaseUiSchemaFactoryServices[$fundingCaseType],
          ],
        );

        $fundingCaseFormDataGetHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          FundingCaseFormDataGetHandler::class,
          ['$formDataFactory' => $fundingCaseFormDataFactoryServices[$fundingCaseType]],
        );

        $fundingCaseFormUpdateSubmitHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          FundingCaseFormUpdateSubmitHandler::class,
          [
            '$applicationAllowedActionApplier' => $this->createService(
              $container,
              $fundingCaseType,
              ApplicationAllowedActionApplier::class,
              ['$actionsDeterminer' => $applicationActionsDeterminerServices[$fundingCaseType]],
            ),
            '$statusDeterminer' => $fundingCaseStatusDeterminerServices[$fundingCaseType],
          ],
        );

        $fundingCaseFormUpdateValidateHandlerServices[$fundingCaseType] ??= $this->createService(
          $container,
          $fundingCaseType,
          FundingCaseFormUpdateValidateHandler::class,
          [
            '$validator' => $fundingCaseValidatorServices[$fundingCaseType],
          ],
        );
      }

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
        ['$validator' => $applicationValidator[$fundingCaseType]]
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
        [],
        [ApplicationFormSubmitEventDecorator::class => []],
      );

      $applicationActionApplyHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationActionApplyHandler::class,
        [
          '$commentPersistHandler' => $applicationFormCommentPersistHandlerServices[$fundingCaseType],
          '$info' => $applicationActionStatusInfoServices[$fundingCaseType],
          '$statusDeterminer' => $applicationStatusDeterminerServices[$fundingCaseType],
        ]
      );

      $applicationCostItemsPersistHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationCostItemsPersistHandler::class,
        []
      );

      $applicationResourcesItemsPersistHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationResourcesItemsPersistHandler::class,
        []
      );

      $applicationFilesAddIdentifiersHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFilesAddIdentifiersHandler::class,
        ['$formFilesFactory' => $applicationFormFilesFactoryServices[$fundingCaseType]]
      );

      $applicationFilesPersistHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        ApplicationFilesPersistHandler::class,
        ['$formFilesFactory' => $applicationFormFilesFactoryServices[$fundingCaseType]]
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

      $fundingCaseUpdateAmountApprovedHandlerServices[$fundingCaseType] ??= $this->createService(
        $container,
        $fundingCaseType,
        FundingCaseUpdateAmountApprovedHandler::class,
        [
          '$actionsDeterminer' => $fundingCaseActionsDeterminerServices[$fundingCaseType],
          '$statusDeterminer' => $fundingCaseStatusDeterminerServices[$fundingCaseType],
        ],
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
        ApplicationAllowedActionsGetHandlerInterface::class
        => $applicationAllowedActionsGetHandlerServices[$fundingCaseType],
        ApplicationActionApplyHandlerInterface::class => $applicationActionApplyHandlerServices[$fundingCaseType],
        ApplicationDeleteHandlerInterface::class => $applicationDeleteHandlerServices[$fundingCaseType],
        ApplicationFilesAddIdentifiersHandlerInterface::class
        => $applicationFilesAddIdentifiersHandlerServices[$fundingCaseType],
        ApplicationFilesPersistHandlerInterface::class => $applicationFilesPersistHandlerServices[$fundingCaseType],
        ApplicationFormCreateHandlerInterface::class
        => $applicationFormCreateHandlerServices[$fundingCaseType],
        ApplicationFormDataGetHandlerInterface::class => $applicationFormDataGetHandlerServices[$fundingCaseType],
        ApplicationFormValidateHandlerInterface::class
        => $applicationFormValidateHandlerServices[$fundingCaseType],
        ApplicationFormSubmitHandlerInterface::class => $applicationFormSubmitHandlerServices[$fundingCaseType],
        ApplicationJsonSchemaGetHandlerInterface::class
        => $applicationFormJsonSchemaGetHandlerServices[$fundingCaseType],
        ApplicationCostItemsPersistHandlerInterface::class
        => $applicationCostItemsPersistHandlerServices[$fundingCaseType],
        ApplicationResourcesItemsPersistHandlerInterface::class
        => $applicationResourcesItemsPersistHandlerServices[$fundingCaseType],
        ApplicationSnapshotCreateHandlerInterface::class => $applicationSnapshotCreateHandlerServices[$fundingCaseType],
        ApplicationProcessStatusDeterminerInterface::class => $applicationStatusDeterminerServices[$fundingCaseType],
        FundingCaseApproveHandlerInterface::class => $fundingCaseApproveHandlerServices[$fundingCaseType],
        FundingCaseStatusDeterminerInterface::class => $fundingCaseStatusDeterminerServices[$fundingCaseType],
        FundingCasePossibleActionsGetHandlerInterface::class
        => $fundingCasePossibleActionsGetHandlerServices[$fundingCaseType],
        FundingCaseUpdateAmountApprovedHandlerInterface::class
        => $fundingCaseUpdateAmountApprovedHandlerServices[$fundingCaseType],
        TransferContractRecreateHandlerInterface::class => $transferContractRecreateHandlerServices[$fundingCaseType],
        TransferContractRenderHandlerInterface::class => $transferContractRenderHandlerServices[$fundingCaseType],
      ];

      if (isset($applicationFormNewCreateHandlerServices[$fundingCaseType])) {
        $services[ApplicationFormNewCreateHandlerInterface::class] =
          $applicationFormNewCreateHandlerServices[$fundingCaseType];
        $services[ApplicationFormNewValidateHandlerInterface::class] =
          $applicationFormNewValidateHandlerServices[$fundingCaseType];
        $services[ApplicationFormNewSubmitHandlerInterface::class] =
          $applicationFormNewSubmitHandlerServices[$fundingCaseType];
      }

      if (isset($applicationFormAddCreateHandlerServices[$fundingCaseType])) {
        $services[ApplicationFormAddCreateHandlerInterface::class] =
          $applicationFormAddCreateHandlerServices[$fundingCaseType];
        $services[ApplicationFormAddValidateHandlerInterface::class] =
          $applicationFormAddValidateHandlerServices[$fundingCaseType];
        $services[ApplicationFormAddSubmitHandlerInterface::class] =
          $applicationFormAddSubmitHandlerServices[$fundingCaseType];

        $services[FundingCaseFormNewGetHandlerInterface::class] =
          $fundingCaseFormNewGetHandlerServices[$fundingCaseType];
        $services[FundingCaseFormNewSubmitHandlerInterface::class] =
          $fundingCaseFormNewSubmitHandlerServices[$fundingCaseType];
        $services[FundingCaseFormNewValidateHandlerInterface::class] =
          $fundingCaseFormNewValidateHandlerServices[$fundingCaseType];
      }

      if (isset($fundingCaseFormUpdateGetHandlerServices[$fundingCaseType])) {
        $services[FundingCaseFormUpdateGetHandlerInterface::class] =
          $fundingCaseFormUpdateGetHandlerServices[$fundingCaseType];
        $services[FundingCaseFormDataGetHandlerInterface::class] =
          $fundingCaseFormDataGetHandlerServices[$fundingCaseType];
        $services[FundingCaseFormUpdateValidateHandlerInterface::class] =
          $fundingCaseFormUpdateValidateHandlerServices[$fundingCaseType];
        $services[FundingCaseFormUpdateSubmitHandlerInterface::class] =
          $fundingCaseFormUpdateSubmitHandlerServices[$fundingCaseType];
      }

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
   * @phpstan-return class-string
   */
  private function getServiceClass(ContainerBuilder $container, string $id): string {
    $definition = $container->getDefinition($id);

    /** @phpstan-var class-string $class */
    $class = $definition->getClass() ?? $id;

    return $class;
  }

  private function isServiceReferenceInstanceOf(
    ContainerBuilder $container,
    ?Reference $reference,
    string $class
  ): bool {
    if (NULL === $reference) {
      return FALSE;
    }

    return is_a($this->getServiceClass($container, (string) $reference), $class, TRUE);
  }

}
