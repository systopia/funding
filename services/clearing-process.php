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

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\ApplicationProcess\Clearing\ClearingGroupExtractor;
use Civi\Funding\ApplicationProcess\Clearing\CostItem\ClearableCostItemsLoader;
use Civi\Funding\ApplicationProcess\Clearing\CostItem\ClearingCostItemsJsonFormsGenerator;
use Civi\Funding\ApplicationProcess\Clearing\ItemDetailsFormElementGenerator;
use Civi\Funding\ApplicationProcess\Clearing\ResourcesItem\ClearableResourcesItemsLoader;
use Civi\Funding\ApplicationProcess\Clearing\ResourcesItem\ClearingResourcesItemsJsonFormsGenerator;
use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\ClearingProcess\ClearingFormsGenerator;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingStatusDeterminer;
use Civi\Funding\ClearingProcess\Handler\ClearingFormDataGetHandler;
use Civi\Funding\ClearingProcess\Handler\ClearingFormDataGetHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\ClearingFormGetHandler;
use Civi\Funding\ClearingProcess\Handler\ClearingFormGetHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\ClearingFormValidateHandler;
use Civi\Funding\ClearingProcess\Handler\ClearingFormValidateHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingCostItemsFormDataPersister;
use Civi\Funding\ClearingProcess\Handler\Helper\ClearingResourcesItemsFormDataPersister;
use Civi\Funding\ClearingProcess\ReportDataFormFactory;
use Civi\Funding\ClearingProcess\ReportDataFormFactoryInterface;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

$container->autowire(ClearingProcessManager::class);
$container->autowire(ClearingProcessBundleLoader::class);

$container->autowire(ClearingFormsGenerator::class);
$container->autowire(ClearingCostItemsJsonFormsGenerator::class);
$container->autowire(ClearingResourcesItemsJsonFormsGenerator::class);

$container->autowire(ClearableCostItemsLoader::class);
$container->autowire(ClearableResourcesItemsLoader::class);
$container->autowire(ClearingGroupExtractor::class);
$container->autowire(ItemDetailsFormElementGenerator::class);

$container->autowire(ClearingCostItemsFormDataPersister::class);
$container->autowire(ClearingResourcesItemsFormDataPersister::class);

$container->autowire(ClearingActionsDeterminer::class);
$container->autowire(ClearingStatusDeterminer::class);

$container->autowire(ReportDataFormFactoryInterface::class, ReportDataFormFactory::class)
  ->addArgument(new ServiceLocatorArgument(
    new TaggedIteratorArgument(
      ReportDataFormFactoryInterface::SERVICE_TAG,
      'supported_funding_case_type',
      'getSupportedFundingCaseType'
    )
  ));

$container->autowire(ClearingFormDataGetHandlerInterface::class, ClearingFormDataGetHandler::class)
  ->addTag(ClearingFormDataGetHandlerInterface::SERVICE_TAG);

$container->autowire(ClearingFormGetHandlerInterface::class, ClearingFormGetHandler::class)
  ->addTag(ClearingFormGetHandlerInterface::SERVICE_TAG);

$container->autowire(ClearingFormValidateHandlerInterface::class, ClearingFormValidateHandler::class)
  ->addTag(ClearingFormValidateHandlerInterface::SERVICE_TAG);

$container->autowire(\Civi\Funding\Api4\Action\FundingClearingProcess\GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(\Civi\Funding\Api4\Action\FundingClearingCostItem\GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(\Civi\Funding\Api4\Action\FundingClearingResourcesItem\GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/ClearingProcess/Api4/ActionHandler',
  'Civi\\Funding\\ClearingProcess\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);
