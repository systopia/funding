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

use Civi\Core\CiviEventDispatcher;
use Civi\RemoteTools\Api3\Api3;
use Civi\RemoteTools\Api3\Api3Interface;
use Civi\RemoteTools\Api4\Api4;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\OptionsLoader;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoader;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;
use Civi\RemoteTools\EventSubscriber\ApiAuthorizeInitRequestSubscriber;
use Civi\RemoteTools\EventSubscriber\ApiAuthorizeSubscriber;
use Civi\RemoteTools\EventSubscriber\CheckAccessSubscriber;
use Psr\SimpleCache\CacheInterface;

$container->setAlias(CiviEventDispatcher::class, 'dispatcher.boot');
$container->setAlias(CacheInterface::class, 'cache.long');

$container->register(Api4Interface::class, Api4::class);
$container->register(Api3Interface::class, Api3::class);

$container->autowire(OptionsLoaderInterface::class, OptionsLoader::class);
$container->autowire(PossiblePermissionsLoaderInterface::class, PossiblePermissionsLoader::class);

$container->register(ApiAuthorizeInitRequestSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->register(ApiAuthorizeSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(CheckAccessSubscriber::class)
  ->addTag('kernel.event_subscriber');
