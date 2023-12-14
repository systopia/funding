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

use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationActionsDeterminer;
use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationActionStatusInfo;
use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationStatusDeterminer;
use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationSubmitActionsContainer;
use Civi\Funding\SammelantragKurs\Application\Actions\KursApplicationSubmitActionsFactory;
use Civi\Funding\SammelantragKurs\Application\Data\KursApplicationFormDataFactory;
use Civi\Funding\SammelantragKurs\Application\Data\KursApplicationFormFilesFactory;
use Civi\Funding\SammelantragKurs\Application\Data\KursApplicationResourcesItemsFactory;
use Civi\Funding\SammelantragKurs\Application\JsonSchema\KursApplicationJsonSchemaFactory;
use Civi\Funding\SammelantragKurs\Application\UiSchema\KursApplicationUiSchemaFactory;
use Civi\Funding\SammelantragKurs\Application\Validation\KursApplicationValidator;
use Civi\Funding\SammelantragKurs\EventSubscriber\KursApplicationStatusSubscriber;
use Civi\Funding\SammelantragKurs\FundingCase\Actions\KursCaseActionsDeterminer;
use Civi\Funding\SammelantragKurs\FundingCase\Actions\KursCaseSubmitActionsContainer;
use Civi\Funding\SammelantragKurs\FundingCase\Actions\KursCaseSubmitActionsFactory;
use Civi\Funding\SammelantragKurs\FundingCase\Data\KursCaseFormDataFactory;
use Civi\Funding\SammelantragKurs\FundingCase\JsonSchema\KursCaseJsonSchemaFactory;
use Civi\Funding\SammelantragKurs\FundingCase\UiSchema\KursCaseUiSchemaFactory;
use Civi\Funding\SammelantragKurs\FundingCase\Validation\KursCaseValidator;
use Symfony\Component\DependencyInjection\Reference;

$container->autowire(KursApplicationActionsDeterminer::class)
  ->addTag(KursApplicationActionsDeterminer::SERVICE_TAG);
$container->autowire(KursApplicationActionStatusInfo::class)
  ->addTag(KursApplicationActionStatusInfo::SERVICE_TAG);
$container->autowire(KursApplicationStatusDeterminer::class)
  ->addTag(KursApplicationStatusDeterminer::SERVICE_TAG);
$container->autowire(KursApplicationSubmitActionsContainer::class)
  ->addTag(KursApplicationSubmitActionsContainer::SERVICE_TAG);
$container->autowire(KursApplicationSubmitActionsFactory::class)
  ->addTag(KursApplicationSubmitActionsFactory::SERVICE_TAG);

$container->autowire(KursCaseActionsDeterminer::class)
  ->addTag(KursCaseActionsDeterminer::SERVICE_TAG);
$container->autowire(KursCaseSubmitActionsContainer::class);
$container->autowire(KursCaseSubmitActionsFactory::class);

$container->autowire(KursCaseFormDataFactory::class)
  ->addTag(KursCaseFormDataFactory::SERVICE_TAG);
$container->autowire(KursCaseUiSchemaFactory::class)
  ->addTag(KursCaseUiSchemaFactory::SERVICE_TAG);
$container->autowire(KursCaseJsonSchemaFactory::class)
  ->addTag(KursCaseJsonSchemaFactory::SERVICE_TAG);
$container->autowire(KursCaseValidator::class)
  ->setArgument('$jsonSchemaFactory', new Reference(KursCaseJsonSchemaFactory::class))
  ->addTag(KursCaseValidator::SERVICE_TAG);

$container->autowire(KursApplicationJsonSchemaFactory::class)
  ->addTag(KursApplicationJsonSchemaFactory::SERVICE_TAG);
$container->autowire(KursApplicationUiSchemaFactory::class)
  ->addTag(KursApplicationUiSchemaFactory::SERVICE_TAG);
$container->autowire(KursApplicationFormDataFactory::class)
  ->addTag(KursApplicationFormDataFactory::SERVICE_TAG);
$container->autowire(KursApplicationValidator::class)
  ->setArgument('$jsonSchemaFactory', new Reference(KursApplicationJsonSchemaFactory::class))
  ->addTag(KursApplicationValidator::SERVICE_TAG);
$container->autowire(KursApplicationResourcesItemsFactory::class)
  ->addTag(KursApplicationResourcesItemsFactory::SERVICE_TAG);
$container->autowire(KursApplicationFormFilesFactory::class)
  ->addTag(KursApplicationFormFilesFactory::SERVICE_TAG);

$container->autowire(KursApplicationStatusSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
