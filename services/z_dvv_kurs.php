<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\DependencyInjection\Util\TaskServiceRegistrator;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\Actions\KursApplicationActionsDeterminer;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\Actions\KursApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\Data\KursApplicationFormFilesFactory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\Data\KursFormDataFactory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\Data\KursProjektunterlagenFactory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema\KursJsonSchemaFactory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema\KursStatusMarkupFactory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\UISchema\KursUiSchemaFactory;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\DvvKursMetaData;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\FundingCase\Actions\KursCaseActionsDeterminer;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Report\KursReportDataLoader;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Report\KursReportFormFactory;

$container->autowire(DvvKursMetaData::class)
  ->addTag(FundingCaseTypeMetaDataInterface::class);

$container->autowire(KursApplicationActionsDeterminer::class)
  ->addTag(KursApplicationActionsDeterminer::SERVICE_TAG);

$container->autowire(KursStatusMarkupFactory::class);
$container->autowire(KursJsonSchemaFactory::class)
  ->addTag(KursJsonSchemaFactory::SERVICE_TAG);
$container->autowire(KursUiSchemaFactory::class)
  ->addTag(KursUiSchemaFactory::SERVICE_TAG);
$container->autowire(KursFormDataFactory::class)
  ->addTag(KursFormDataFactory::SERVICE_TAG);
$container->autowire(KursProjektunterlagenFactory::class);
$container->autowire(KursApplicationFormFilesFactory::class)
  ->addTag(KursApplicationFormFilesFactory::SERVICE_TAG);

$container->autowire(KursApplicationStatusDeterminer::class)
  ->addTag(KursApplicationStatusDeterminer::SERVICE_TAG);

$container->autowire(KursCaseActionsDeterminer::class)
  ->addTag(KursCaseActionsDeterminer::SERVICE_TAG);

$container->autowire(KursReportDataLoader::class)
  ->addTag(KursReportDataLoader::SERVICE_TAG);
$container->autowire(KursReportFormFactory::class)
  ->addTag(KursReportFormFactory::SERVICE_TAG);

TaskServiceRegistrator::autowireAll(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCaseTypes/DVV/Kurs/Task',
  'Civi\\Funding\\FundingCaseTypes\\DVV\\Kurs\\Task'
);
