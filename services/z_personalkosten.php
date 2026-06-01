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

use Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorInterface;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\DependencyInjection\Util\TaskServiceRegistrator;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Actions\PersonalkostenApplicationActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Actions\PersonalkostenApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Data\PersonalkostenApplicationFormDataFactory;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Data\PersonalkostenApplicationFormFilesFactory;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Data\PersonalkostenDokumenteFactory;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\JsonSchema\PersonalkostenApplicationJsonSchemaFactory;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\PersonalkostenApplicationProcessUpdater;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\UiSchema\PersonalkostenApplicationUiSchemaFactory;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\PersonalkostenClearingReceiptsFormGenerator;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\PersonalkostenReportDataLoader;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\PersonalkostenReportFormFactory;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\FundingCase\Actions\PersonalkostenCaseActionsDeterminer;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\PersonalkostenMetaData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

$container->autowire(PersonalkostenMetaData::class)
  ->addTag(FundingCaseTypeMetaDataInterface::class);

$container->autowire(PersonalkostenApplicationActionsDeterminer::class)
  ->addTag(PersonalkostenApplicationActionsDeterminer::SERVICE_TAG);

$container->autowire(PersonalkostenApplicationFormDataFactory::class)
  ->addTag(PersonalkostenApplicationFormDataFactory::SERVICE_TAG);
$container->autowire(PersonalkostenApplicationFormFilesFactory::class)
  ->addTag(PersonalkostenApplicationFormFilesFactory::SERVICE_TAG);
$container->autowire(PersonalkostenDokumenteFactory::class);

$container->autowire(PersonalkostenApplicationJsonSchemaFactory::class)
  ->addTag(PersonalkostenApplicationJsonSchemaFactory::SERVICE_TAG);
$container->autowire(PersonalkostenApplicationUiSchemaFactory::class)
  ->addTag(PersonalkostenApplicationUiSchemaFactory::SERVICE_TAG);

$container->autowire(PersonalkostenApplicationStatusDeterminer::class)
  ->addTag(PersonalkostenApplicationStatusDeterminer::SERVICE_TAG);

$container->autowire(PersonalkostenApplicationProcessUpdater::class);

$container->autowire(PersonalkostenClearingReceiptsFormGenerator::class)
  ->addTag(ReceiptsFormGeneratorInterface::class);
$container->autowire(PersonalkostenReportFormFactory::class)
  ->addTag(PersonalkostenReportFormFactory::SERVICE_TAG);
$container->autowire(PersonalkostenReportDataLoader::class)
  ->addTag(PersonalkostenReportDataLoader::SERVICE_TAG);

$container->autowire(PersonalkostenCaseActionsDeterminer::class)
  ->addTag(PersonalkostenCaseActionsDeterminer::SERVICE_TAG);

TaskServiceRegistrator::autowireAll(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCaseTypes/AuL/Personalkosten/Task',
  'Civi\\Funding\\FundingCaseTypes\\AuL\\Personalkosten\\Task'
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCaseTypes/AuL/Personalkosten/EventSubscriber',
  'Civi\\Funding\\FundingCaseTypes\\AuL\\Personalkosten\\EventSubscriber',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => 'auto'],
);
