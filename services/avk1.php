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

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ReworkPossibleApplicationProcessActionsDeterminer;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ReworkPossibleApplicationProcessStatusDeterminer;
use Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1ApplicationResourcesItemSubscriber;
use Civi\Funding\Form\ApplicationSubmitActionsFactory;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormDataFactory;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1JsonSchemaFactory;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1UiSchemaFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationCostItemsFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationResourcesItemsFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1FinanzierungFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1KostenFactory;
use Symfony\Component\DependencyInjection\Reference;

$container->autowire(AVK1ApplicationResourcesItemSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);

$container->autowire('funding.avk1.application_submit_actions_factory', ApplicationSubmitActionsFactory::class)
  ->setArgument('$actionsDeterminer', new Reference(ReworkPossibleApplicationProcessActionsDeterminer::class))
  ->setArgument('$submitActionsContainer', new Reference('funding.application.submit_actions_container'));

$container->autowire(AVK1JsonSchemaFactory::class)
  ->setArgument('$actionsDeterminer', new Reference(ReworkPossibleApplicationProcessActionsDeterminer::class))
  ->addTag('funding.application.json_schema_factory');
$container->autowire(AVK1UiSchemaFactory::class)
  ->setArgument('$submitActionsFactory', new Reference('funding.avk1.application_submit_actions_factory'))
  ->addTag('funding.application.ui_schema_factory');
$container->autowire(AVK1FormDataFactory::class)
  ->addTag('funding.application.form_data_factory');
$container->autowire(AVK1KostenFactory::class);
$container->autowire(AVK1FinanzierungFactory::class);
$container->autowire(AVK1ApplicationCostItemsFactory::class)
  ->addTag('funding.application.cost_items_factory');
$container->autowire(AVK1ApplicationResourcesItemsFactory::class)
  ->addTag('funding.application.resources_items_factory');

$container->getDefinition(ReworkPossibleApplicationProcessStatusDeterminer::class)
  ->addTag('funding.application.status_determiner', ['funding_case_type' => 'AVK1SonstigeAktivitaet']);
