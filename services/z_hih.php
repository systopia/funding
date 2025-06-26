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

use Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorInterface;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationActionsDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationActionStatusInfo;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationSubmitActionsContainer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationSubmitActionsFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Data\HiHApplicationFormDataFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Data\HiHApplicationFormFilesFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Data\HiHInfoDateienFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema\HiHApplicationFormValidator;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema\HiHApplicationJsonSchemaFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\UiSchema\HiHApplicationUiSchemaFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\HiHClearingReceiptsFormGenerator;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\HiHReportDataLoader;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\HiHReportFormFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase\Actions\HiHCaseActionsDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase\HiHPossibleRecipientsForChangeLoader;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase\StatusDeterminer\HiHCaseStatusDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\HiHMetaData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

$container->autowire(HiHMetaData::class)
  ->addTag(FundingCaseTypeMetaDataInterface::class);

$container->autowire(HiHApplicationActionsDeterminer::class)
  ->addTag(HiHApplicationActionsDeterminer::SERVICE_TAG);

$container->autowire(HiHApplicationActionStatusInfo::class)
  ->addTag(HiHApplicationActionStatusInfo::SERVICE_TAG);

$container->autowire(HiHApplicationStatusDeterminer::class)
  ->addTag(HiHApplicationStatusDeterminer::SERVICE_TAG);

$container->autowire(HiHApplicationSubmitActionsContainer::class)
  ->addTag(HiHApplicationSubmitActionsContainer::SERVICE_TAG);

$container->autowire(
  HiHApplicationSubmitActionsFactory::class)
  ->addTag(HiHApplicationSubmitActionsFactory::SERVICE_TAG);

$container->autowire(HiHApplicationJsonSchemaFactory::class)
  ->addTag(HiHApplicationJsonSchemaFactory::SERVICE_TAG);

$container->autowire(HiHApplicationUiSchemaFactory::class)
  ->addTag(HiHApplicationUiSchemaFactory::SERVICE_TAG);

$container->autowire(HiHCaseActionsDeterminer::class)
  ->addTag(HiHCaseActionsDeterminer::SERVICE_TAG);

$container->autowire(HiHCaseStatusDeterminer::class)
  ->addTag(HiHCaseStatusDeterminer::SERVICE_TAG);

$container->autowire(HiHApplicationFormDataFactory::class)
  ->addTag(HiHApplicationFormDataFactory::SERVICE_TAG);

$container->autowire(HiHApplicationFormFilesFactory::class)
  ->addTag(HiHApplicationFormFilesFactory::SERVICE_TAG);

$container->autowire(HiHInfoDateienFactory::class);

$container->autowire(HiHApplicationFormValidator::class)
  ->addTag(HiHApplicationFormValidator::SERVICE_TAG);

$container->autowire(HiHPossibleRecipientsForChangeLoader::class)
  ->addTag(HiHPossibleRecipientsForChangeLoader::SERVICE_TAG);

$container->autowire(HiHClearingReceiptsFormGenerator::class)
  ->addTag(ReceiptsFormGeneratorInterface::class);
$container->autowire(HiHReportFormFactory::class)
  ->addTag(HiHReportFormFactory::SERVICE_TAG);
$container->autowire(HiHReportDataLoader::class)
  ->addTag(HiHReportDataLoader::SERVICE_TAG);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCaseTypes/BSH/HiHAktion/EventSubscriber',
  'Civi\\Funding\\FundingCaseTypes\\BSH\\HiHAktion\\EventSubscriber',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => 'auto'],
);
