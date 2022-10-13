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

use Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1ApplicationCostItemSubscriber;
use Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1ApplicationResourcesItemSubscriber;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationCostItemsFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationResourcesItemsFactory;

$container->autowire(AVK1ApplicationCostItemSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);

$container->autowire(AVK1ApplicationResourcesItemSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);

$container->autowire(AVK1FormFactory::class)
  ->addTag('funding.form_factory');
$container->autowire(AVK1ApplicationCostItemsFactory::class);
$container->autowire(AVK1ApplicationResourcesItemsFactory::class);
