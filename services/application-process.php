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

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationExternalFileManager;
use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGenerator;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGeneratorInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ApplicationProcess\ApplicationSnapshotManager;
use Civi\Funding\ApplicationProcess\EligibleApplicationProcessesLoader;
use Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationAllowedActionsGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationJsonSchemaGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationActionApplyHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationAllowedActionsGetHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationCostItemsPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationDeleteHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFilesAddIdentifiersHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFilesPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormAddCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormAddSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormDataGetHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormNewCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormNewSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationJsonSchemaGetHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationResourcesItemsPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationSnapshotCreateHandler;
use Civi\Funding\ApplicationProcess\Helper\ApplicationJsonSchemaCreateHelper;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidator;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\ApplicationSchemaValidatorInterface;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidator;
use Civi\Funding\ApplicationProcess\JsonSchema\Validator\OpisApplicationValidatorFactory;
use Civi\Funding\ApplicationProcess\Snapshot\ApplicationSnapshotRestorer;
use Civi\Funding\ApplicationProcess\Snapshot\ApplicationSnapshotRestorerInterface;
use Civi\Funding\DependencyInjection\ApplicationFormValidatorPass;
use Civi\Funding\DependencyInjection\Compiler\ApplicationJsonSchemaFactoryPass;
use Civi\Funding\DependencyInjection\Compiler\ApplicationUiSchemaFactoryPass;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\Form\Application\ApplicationCostItemsFormDataLoader;
use Civi\Funding\Form\Application\ApplicationCostItemsFormDataLoaderInterface;
use Civi\Funding\Form\Application\ApplicationResourcesItemsFormDataLoader;
use Civi\Funding\Form\Application\ApplicationResourcesItemsFormDataLoaderInterface;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactory;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\FundingCase\StatusDeterminer\DefaultFundingCaseStatusDeterminer;
use Civi\Funding\Validation\ConcreteEntityValidatorInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

$container->addCompilerPass(new ApplicationFormValidatorPass());
$container->addCompilerPass(new ApplicationJsonSchemaFactoryPass());
$container->addCompilerPass(new ApplicationUiSchemaFactoryPass());

$container->autowire(ApplicationProcessManager::class)
  // Used in API actions.
  ->setPublic(TRUE);
$container->autowire(ApplicationProcessBundleLoader::class)
  // Used in API actions.
  ->setPublic(TRUE);
$container->autowire(ApplicationCostItemManager::class);
$container->autowire(ApplicationResourcesItemManager::class);
$container->autowire(ApplicationExternalFileManagerInterface::class, ApplicationExternalFileManager::class);
$container->autowire(ApplicationIdentifierGeneratorInterface::class, ApplicationIdentifierGenerator::class);
$container->autowire(ApplicationProcessActivityManager::class);
$container->autowire(EligibleApplicationProcessesLoader::class);
$container->autowire(ApplicationSnapshotManager::class);

$container->autowire(DefaultFundingCaseStatusDeterminer::class);

$container->autowire(ApplicationCostItemsFormDataLoaderInterface::class, ApplicationCostItemsFormDataLoader::class);
$container->autowire(
  ApplicationResourcesItemsFormDataLoaderInterface::class,
  ApplicationResourcesItemsFormDataLoader::class
);

$container->autowire(ApplicationJsonSchemaCreateHelper::class);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/ApplicationProcess/Validator',
  'Civi\\Funding\\ApplicationProcess\\Validator',
  ConcreteEntityValidatorInterface::class,
  ['funding.validator.entity' => []]
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/ApplicationProcess/Api4/ActionHandler',
  'Civi\\Funding\\ApplicationProcess\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

$container->register(OpisApplicationValidator::class, OpisApplicationValidator::class)
  ->setFactory([OpisApplicationValidatorFactory::class, 'getValidator']);
$container->autowire(ApplicationSchemaValidatorInterface::class, ApplicationSchemaValidator::class);

$container->autowire(ApplicationSubmitActionsFactoryInterface::class, ApplicationSubmitActionsFactory::class);

$container->autowire(ApplicationActionApplyHandlerInterface::class, DefaultApplicationActionApplyHandler::class);
$container->autowire(
  ApplicationAllowedActionsGetHandlerInterface::class,
  DefaultApplicationAllowedActionsGetHandler::class
);
$container->autowire(ApplicationDeleteHandlerInterface::class, DefaultApplicationDeleteHandler::class)
  // Used in API action.
  ->setPublic(TRUE);

$container->autowire(ApplicationFormNewCreateHandlerInterface::class, DefaultApplicationFormNewCreateHandler::class);
$container->autowire(ApplicationFormNewValidateHandlerInterface::class, ApplicationFormNewValidateHandler::class);
$container->autowire(ApplicationFormNewSubmitHandlerInterface::class, DefaultApplicationFormNewSubmitHandler::class);

$container->autowire(ApplicationFormAddCreateHandlerInterface::class, DefaultApplicationFormAddCreateHandler::class);
$container->autowire(ApplicationFormAddValidateHandlerInterface::class, ApplicationFormAddValidateHandler::class);
$container->autowire(ApplicationFormAddSubmitHandlerInterface::class, DefaultApplicationFormAddSubmitHandler::class);

$container->autowire(ApplicationFormDataGetHandlerInterface::class, DefaultApplicationFormDataGetHandler::class)
  // Used in API action.
  ->setPublic(TRUE);
$container->autowire(ApplicationFormCreateHandlerInterface::class, DefaultApplicationFormCreateHandler::class);
$container->autowire(ApplicationFormValidateHandlerInterface::class, ApplicationFormValidateHandler::class)
  // Used in API action.
  ->setPublic(TRUE);
$container->autowire(ApplicationFormSubmitHandlerInterface::class, DefaultApplicationFormSubmitHandler::class)
  // Used in API action.
  ->setPublic(TRUE);

$container->autowire(ApplicationJsonSchemaGetHandlerInterface::class, DefaultApplicationJsonSchemaGetHandler::class)
  // Used in API action.
  ->setPublic(TRUE);
$container->autowire(
  ApplicationCostItemsPersistHandlerInterface::class,
  DefaultApplicationCostItemsPersistHandler::class
);
$container->autowire(
  ApplicationResourcesItemsPersistHandlerInterface::class,
  DefaultApplicationResourcesItemsPersistHandler::class
);
$container->autowire(
  ApplicationFilesAddIdentifiersHandlerInterface::class,
  DefaultApplicationFilesAddIdentifiersHandler::class
);
$container->autowire(ApplicationFilesPersistHandlerInterface::class, DefaultApplicationFilesPersistHandler::class);
$container->autowire(ApplicationSnapshotCreateHandlerInterface::class, DefaultApplicationSnapshotCreateHandler::class);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/ApplicationProcess/Remote/Api4/ActionHandler',
  'Civi\\Funding\\ApplicationProcess\\Remote\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/EventSubscriber/ApplicationProcess',
  'Civi\\Funding\\EventSubscriber\\ApplicationProcess',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => 'auto'],
);

$container->autowire(ApplicationSnapshotRestorerInterface::class, ApplicationSnapshotRestorer::class);
