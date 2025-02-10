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

use Civi\Funding\Upgrade\Upgrader0002;
use Civi\Funding\Upgrade\Upgrader0003;
use Civi\Funding\Upgrade\Upgrader0006;
use Civi\Funding\Upgrade\Upgrader0008;
use Civi\Funding\Upgrade\Upgrader0009;
use Civi\Funding\Upgrade\Upgrader0010;

$container->autowire(Upgrader0002::class)
  ->setPublic(TRUE);

$container->autowire(Upgrader0003::class)
  ->setPublic(TRUE);

$container->autowire(Upgrader0006::class)
  ->setPublic(TRUE);

$container->autowire(Upgrader0008::class)
  ->setPublic(TRUE);

$container->autowire(Upgrader0009::class)
  ->setPublic(TRUE);

$container->autowire(Upgrader0010::class)
  ->setPublic(TRUE);
