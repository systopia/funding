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

use Civi\RemoteTools\Api4\OptionsLoader;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoader;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;
use Civi\RemoteTools\Database\TransactionFactory;

$container->register(TransactionFactory::class);

$container->autowire(OptionsLoaderInterface::class, OptionsLoader::class)
  // Used in API action.
  ->setPublic(TRUE);
$container->autowire(PossiblePermissionsLoaderInterface::class, PossiblePermissionsLoader::class)
  // Used in API action.
  ->setPublic(TRUE);
