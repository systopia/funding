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

use Civi\Funding\Api4\Action\FundingApplicationProcess\GetAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateFormAction;
use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessActionsDeterminer;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessModificationDateSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessGetFieldsSubscriber;

$container->autowire(ApplicationProcessManager::class);
$container->autowire(ApplicationCostItemManager::class);
$container->autowire(ApplicationResourcesItemManager::class);

$container->autowire(GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(GetFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(SubmitFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(ValidateFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(ApplicationProcessGetFieldsSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(ApplicationProcessModificationDateSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessDAOGetSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(ApplicationProcessActionsDeterminer::class);
$container->autowire(ApplicationProcessStatusDeterminer::class);