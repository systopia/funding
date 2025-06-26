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

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\ClearingCostItemManager;
use Civi\Funding\ClearingProcess\ClearingExternalFileManager;
use Civi\Funding\ClearingProcess\ClearingExternalFileManagerInterface;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\ClearingProcess\ClearingStatusDeterminer;
use Civi\Funding\ClearingProcess\Form\ClearingFormGenerator;
use Civi\Funding\ClearingProcess\Form\ClearingGroupExtractor;
use Civi\Funding\ClearingProcess\Form\CostItem\ClearableCostItemsLoader;
use Civi\Funding\ClearingProcess\Form\CostItem\ClearingCostItemsJsonFormsGenerator;
use Civi\Funding\ClearingProcess\Form\ItemDetailsFormElementGenerator;
use Civi\Funding\ClearingProcess\Form\ReceiptsFormGenerator;
use Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorInterface;
use Civi\Funding\ClearingProcess\Form\ResourcesItem\ClearableResourcesItemsLoader;
use Civi\Funding\ClearingProcess\Form\ResourcesItem\ClearingResourcesItemsJsonFormsGenerator;
use Civi\Funding\ClearingProcess\Handler\ClearingActionApplyHandler;
use Civi\Funding\ClearingProcess\Handler\ClearingActionApplyHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\ClearingFormDataGetHandler;
use Civi\Funding\ClearingProcess\Handler\ClearingFormDataGetHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\ClearingFormGetHandler;
use Civi\Funding\ClearingProcess\Handler\ClearingFormGetHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\ClearingFormSubmitHandler;
use Civi\Funding\ClearingProcess\Handler\ClearingFormSubmitHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\ClearingFormValidateHandler;
use Civi\Funding\ClearingProcess\Handler\ClearingFormValidateHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingCommentPersister;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingCostItemsFormDataPersister;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingResourcesItemsFormDataPersister;
use Civi\Funding\DependencyInjection\Compiler\ClearingFormValidatorPass;
use Civi\Funding\DependencyInjection\Compiler\ClearingReceiptsFormFactoryPass;
use Civi\Funding\DependencyInjection\Compiler\ClearingReportDataLoaderPass;
use Civi\Funding\DependencyInjection\Compiler\ClearingReportFormFactoryPass;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

$container->addCompilerPass(new ClearingReceiptsFormFactoryPass());
$container->addCompilerPass(new ClearingReportDataLoaderPass());
$container->addCompilerPass(new ClearingReportFormFactoryPass());
$container->addCompilerPass(new ClearingFormValidatorPass());

$container->autowire(ClearingProcessManager::class)
  // Used in API action.
  ->setPublic(TRUE);
$container->autowire(ClearingProcessBundleLoader::class);
$container->autowire(ClearingCostItemManager::class);
$container->autowire(ClearingResourcesItemManager::class);
$container->autowire(ClearingExternalFileManagerInterface::class, ClearingExternalFileManager::class);

$container->autowire(ClearingFormGenerator::class);
$container->autowire(ReceiptsFormGenerator::class)
  ->addTag(ReceiptsFormGeneratorInterface::class);
$container->autowire(ClearingCostItemsJsonFormsGenerator::class);
$container->autowire(ClearingResourcesItemsJsonFormsGenerator::class);

$container->autowire(ClearableCostItemsLoader::class);
$container->autowire(ClearableResourcesItemsLoader::class);
$container->autowire(ClearingGroupExtractor::class);
$container->autowire(ItemDetailsFormElementGenerator::class);

$container->autowire(ClearingCommentPersister::class);
$container->autowire(ClearingCostItemsFormDataPersister::class);
$container->autowire(ClearingResourcesItemsFormDataPersister::class);

$container->autowire(ClearingActionsDeterminer::class);
$container->autowire(ClearingStatusDeterminer::class);

$container->autowire(ClearingActionApplyHandlerInterface::class, ClearingActionApplyHandler::class)
  ->addTag(ClearingActionApplyHandlerInterface::class);

$container->autowire(ClearingFormDataGetHandlerInterface::class, ClearingFormDataGetHandler::class)
  ->addTag(ClearingFormDataGetHandlerInterface::SERVICE_TAG);

$container->autowire(ClearingFormGetHandlerInterface::class, ClearingFormGetHandler::class);

$container->autowire(ClearingFormValidateHandlerInterface::class, ClearingFormValidateHandler::class)
  ->addTag(ClearingFormValidateHandlerInterface::SERVICE_TAG);

$container->autowire(ClearingFormSubmitHandlerInterface::class, ClearingFormSubmitHandler::class)
  ->addTag(ClearingFormSubmitHandlerInterface::SERVICE_TAG);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/ClearingProcess/Api4/ActionHandler',
  'Civi\\Funding\\ClearingProcess\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/EventSubscriber/ClearingProcess',
  'Civi\\Funding\\EventSubscriber\\ClearingProcess',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => TRUE],
);
