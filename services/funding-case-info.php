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

use Civi\Funding\Api4\Action\FundingCaseInfo\GetAction;
use Civi\Funding\Api4\Action\FundingCaseInfo\GetFieldsAction;
use Civi\Funding\EventSubscriber\Remote\FundingCaseInfoGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseInfoGetSubscriber;

$container->autowire(GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(FundingCaseInfoGetSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingCaseInfoGetFieldsSubscriber::class)
  ->addTag('kernel.event_subscriber');
