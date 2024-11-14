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

use Civi\Funding\DependencyInjection\Compiler\FundingTaskPass;
use Civi\Funding\Task\FundingTaskManager;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

$container->addCompilerPass(new FundingTaskPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);

$container->autowire(FundingTaskManager::class);

$container->autowire(\Civi\Funding\Task\Api4\ActionHandler\RemoteGetActionHandler::class)
  ->addTag(\Civi\Funding\Task\Api4\ActionHandler\RemoteGetActionHandler::SERVICE_TAG);
$container->autowire(\Civi\Funding\Task\Api4\ActionHandler\RemoteGetFieldsActionHandler::class)
  ->addTag(\Civi\Funding\Task\Api4\ActionHandler\RemoteGetFieldsActionHandler::SERVICE_TAG);

$container->autowire(\Civi\Funding\Api4\Action\FundingTask\GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
