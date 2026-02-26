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

// This file is prefix by "z_" so it is loaded as last one.

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\DependencyInjection\Util\TaskServiceRegistrator;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\Actions\IJBApplicationActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\Actions\IJBApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\Data\IJBApplicationFormDataFactory;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\Data\IJBApplicationFormFilesFactory;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\Data\IJBProjektunterlagenFactory;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\JsonSchema\IJBApplicationJsonSchemaFactory;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Application\UiSchema\IJBApplicationUiSchemaFactory;
use Civi\Funding\FundingCaseTypes\AuL\IJB\EventSubscriber\IJBAngularModuleSubscriber;
use Civi\Funding\FundingCaseTypes\AuL\IJB\FundingCase\Actions\IJBCaseActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\IJB\IJBMetaData;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\IJBReportDataLoader;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Report\IJBReportFormFactory;

$container->autowire(IJBMetaData::class)
  ->addTag(FundingCaseTypeMetaDataInterface::class);

$container->autowire(IJBApplicationActionsDeterminer::class)
  ->addTag(IJBApplicationActionsDeterminer::SERVICE_TAG);

$container->autowire(IJBApplicationJsonSchemaFactory::class)
  ->addTag(IJBApplicationJsonSchemaFactory::SERVICE_TAG);
$container->autowire(IJBApplicationUiSchemaFactory::class)
  ->addTag(IJBApplicationUiSchemaFactory::SERVICE_TAG);

$container->autowire(IJBApplicationFormDataFactory::class)
  ->addTag(IJBApplicationFormDataFactory::SERVICE_TAG);
$container->autowire(IJBProjektunterlagenFactory::class);

$container->autowire(IJBApplicationFormFilesFactory::class)
  ->addTag(IJBApplicationFormFilesFactory::SERVICE_TAG);

$container->autowire(IJBApplicationStatusDeterminer::class)
  ->addTag(IJBApplicationStatusDeterminer::SERVICE_TAG);

$container->autowire(IJBCaseActionsDeterminer::class)
  ->addTag(IJBCaseActionsDeterminer::SERVICE_TAG);

$container->autowire(IJBReportDataLoader::class)
  ->addTag(IJBReportDataLoader::SERVICE_TAG);
$container->autowire(IJBReportFormFactory::class)
  ->addTag(IJBReportFormFactory::SERVICE_TAG);

TaskServiceRegistrator::autowireAll(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCaseTypes/AuL/IJB/Task',
  'Civi\\Funding\\FundingCaseTypes\\AuL\\IJB\\Task'
);

$container->autowire(IJBAngularModuleSubscriber::class)
  ->addTag('kernel.event_subscriber');
