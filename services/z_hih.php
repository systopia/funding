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

use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationActionsDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationActionStatusInfo;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationStatusDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationSubmitActionsContainer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Actions\HiHApplicationSubmitActionsFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema\HiHApplicationJsonSchemaFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\UiSchema\HiHApplicationUiSchemaFactory;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\EventSubscriber\HiHAngularModuleSubscriber;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase\Actions\HiHCaseActionsDeterminer;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\FundingCase\HiHPossibleRecipientsForChangeLoader;

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

$container->autowire(HiHPossibleRecipientsForChangeLoader::class)
  ->addTag(HiHPossibleRecipientsForChangeLoader::SERVICE_TAG);

$container->autowire(HiHAngularModuleSubscriber::class)
  ->addTag('kernel.event_subscriber');
