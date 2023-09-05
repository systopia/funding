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

use Civi\Funding\ApplicationProcess\ActionStatusInfo\ReworkPossibleApplicationProcessActionStatusInfo;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ReworkPossibleApplicationProcessStatusDeterminer;
use Civi\Funding\Form\ApplicationSubmitActionsFactory;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormDataFactory;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1JsonSchemaFactory;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1StatusMarkupFactory;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1UiSchemaFactory;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1Validator;
use Civi\Funding\SonstigeAktivitaet\Actions\AVK1ApplicationActionsDeterminer;
use Civi\Funding\SonstigeAktivitaet\Actions\AVK1ApplicationSubmitActionsContainer;
use Civi\Funding\SonstigeAktivitaet\Actions\AVK1ApplicationSubmitActionsFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationCostItemsFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationFormFilesFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationResourcesItemsFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1Constants;
use Civi\Funding\SonstigeAktivitaet\AVK1FinanzierungFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1KostenFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ProjektunterlagenFactory;
use Civi\Funding\SonstigeAktivitaet\FundingCase\AVK1CaseActionsDeterminer;
use Symfony\Component\DependencyInjection\Reference;

$container->autowire(AVK1ApplicationSubmitActionsContainer::class)
  ->addTag(AVK1ApplicationSubmitActionsContainer::SERVICE_TAG);
$container->autowire(AVK1ApplicationActionsDeterminer::class)
  ->addTag(AVK1ApplicationActionsDeterminer::SERVICE_TAG);
$container->autowire(AVK1ApplicationSubmitActionsFactory::class)
  ->addTag(ApplicationSubmitActionsFactory::SERVICE_TAG);

$container->autowire(AVK1StatusMarkupFactory::class);
$container->autowire(AVK1JsonSchemaFactory::class)
  ->addTag(AVK1JsonSchemaFactory::SERVICE_TAG);
$container->autowire(AVK1UiSchemaFactory::class)
  ->addTag(AVK1UiSchemaFactory::SERVICE_TAG);
$container->autowire(AVK1FormDataFactory::class)
  ->addTag(AVK1FormDataFactory::SERVICE_TAG);
$container->autowire(AVK1Validator::class)
  ->setArgument('$jsonSchemaFactory', new Reference(AVK1JsonSchemaFactory::class))
  ->addTag(AVK1Validator::SERVICE_TAG);
$container->autowire(AVK1KostenFactory::class);
$container->autowire(AVK1FinanzierungFactory::class);
$container->autowire(AVK1ProjektunterlagenFactory::class);
$container->autowire(AVK1ApplicationCostItemsFactory::class)
  ->addTag(AVK1ApplicationCostItemsFactory::SERVICE_TAG);
$container->autowire(AVK1ApplicationResourcesItemsFactory::class)
  ->addTag(AVK1ApplicationResourcesItemsFactory::SERVICE_TAG);
$container->autowire(AVK1ApplicationFormFilesFactory::class)
  ->addTag(AVK1ApplicationFormFilesFactory::SERVICE_TAG);

$container->getDefinition(ReworkPossibleApplicationProcessStatusDeterminer::class)
  ->addTag(
    ReworkPossibleApplicationProcessStatusDeterminer::SERVICE_TAG,
    ['funding_case_type' => AVK1Constants::FUNDING_CASE_TYPE_NAME]
  );
$container->getDefinition(ReworkPossibleApplicationProcessActionStatusInfo::class)
  ->addTag(
    ReworkPossibleApplicationProcessActionStatusInfo::SERVICE_TAG,
    ['funding_case_type' => AVK1Constants::FUNDING_CASE_TYPE_NAME]
  );

$container->autowire(AVK1CaseActionsDeterminer::class)
  ->addArgument(new Reference(ReworkPossibleApplicationProcessActionStatusInfo::class))
  ->addTag(AVK1CaseActionsDeterminer::SERVICE_TAG);
