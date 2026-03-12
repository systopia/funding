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
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\Actions\AVK1ApplicationActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\Actions\AVK1ApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\Data\AVK1ApplicationFormFilesFactory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\Data\AVK1FormDataFactory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\Data\AVK1ProjektunterlagenFactory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema\AVK1JsonSchemaFactory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema\AVK1StatusMarkupFactory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\UISchema\AVK1UiSchemaFactory;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\AVK1MetaData;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\EventSubscriber\AVK1AngularModuleSubscriber;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\FundingCase\Actions\AVK1CaseActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report\AVK1ReportDataLoader;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Report\AVK1ReportFormFactory;

$container->autowire(AVK1MetaData::class)
  ->addTag(FundingCaseTypeMetaDataInterface::class);

$container->autowire(AVK1ApplicationActionsDeterminer::class)
  ->addTag(AVK1ApplicationActionsDeterminer::SERVICE_TAG);

$container->autowire(AVK1StatusMarkupFactory::class);
$container->autowire(AVK1JsonSchemaFactory::class)
  ->addTag(AVK1JsonSchemaFactory::SERVICE_TAG);
$container->autowire(AVK1UiSchemaFactory::class)
  ->addTag(AVK1UiSchemaFactory::SERVICE_TAG);
$container->autowire(AVK1FormDataFactory::class)
  ->addTag(AVK1FormDataFactory::SERVICE_TAG);
$container->autowire(AVK1ProjektunterlagenFactory::class);
$container->autowire(AVK1ApplicationFormFilesFactory::class)
  ->addTag(AVK1ApplicationFormFilesFactory::SERVICE_TAG);

$container->autowire(AVK1ApplicationStatusDeterminer::class)
  ->addTag(AVK1ApplicationStatusDeterminer::SERVICE_TAG);

$container->autowire(AVK1CaseActionsDeterminer::class)
  ->addTag(AVK1CaseActionsDeterminer::SERVICE_TAG);

$container->autowire(AVK1ReportDataLoader::class)
  ->addTag(AVK1ReportDataLoader::SERVICE_TAG);
$container->autowire(AVK1ReportFormFactory::class)
  ->addTag(AVK1ReportFormFactory::SERVICE_TAG);

TaskServiceRegistrator::autowireAll(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCaseTypes/AuL/SonstigeAktivitaet/Task',
  'Civi\\Funding\\FundingCaseTypes\\AuL\\SonstigeAktivitaet\\Task'
);

$container->autowire(AVK1AngularModuleSubscriber::class)
  ->addTag('kernel.event_subscriber');
