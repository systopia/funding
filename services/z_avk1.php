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

use Civi\Funding\SonstigeAktivitaet\Application\Actions\AVK1ApplicationActionsDeterminer;
use Civi\Funding\SonstigeAktivitaet\Application\Actions\AVK1ApplicationActionStatusInfo;
use Civi\Funding\SonstigeAktivitaet\Application\Actions\AVK1ApplicationStatusDeterminer;
use Civi\Funding\SonstigeAktivitaet\Application\Actions\AVK1ApplicationSubmitActionsContainer;
use Civi\Funding\SonstigeAktivitaet\Application\Actions\AVK1ApplicationSubmitActionsFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ApplicationFormFilesFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ApplicationResourcesItemsFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1FinanzierungFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1FormDataFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Data\AVK1ProjektunterlagenFactory;
use Civi\Funding\SonstigeAktivitaet\Application\JsonSchema\AVK1JsonSchemaFactory;
use Civi\Funding\SonstigeAktivitaet\Application\JsonSchema\AVK1StatusMarkupFactory;
use Civi\Funding\SonstigeAktivitaet\Application\UISchema\AVK1UiSchemaFactory;
use Civi\Funding\SonstigeAktivitaet\Application\Validation\AVK1Validator;
use Civi\Funding\SonstigeAktivitaet\FundingCase\Actions\AVK1CaseActionsDeterminer;
use Symfony\Component\DependencyInjection\Reference;

$container->autowire(AVK1ApplicationSubmitActionsContainer::class)
  ->addTag(AVK1ApplicationSubmitActionsContainer::SERVICE_TAG);
$container->autowire(AVK1ApplicationActionsDeterminer::class)
  ->addTag(AVK1ApplicationActionsDeterminer::SERVICE_TAG);
$container->autowire(AVK1ApplicationSubmitActionsFactory::class)
  ->addTag(AVK1ApplicationSubmitActionsFactory::SERVICE_TAG);

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
$container->autowire(AVK1FinanzierungFactory::class);
$container->autowire(AVK1ProjektunterlagenFactory::class);
$container->autowire(AVK1ApplicationResourcesItemsFactory::class)
  ->addTag(AVK1ApplicationResourcesItemsFactory::SERVICE_TAG);
$container->autowire(AVK1ApplicationFormFilesFactory::class)
  ->addTag(AVK1ApplicationFormFilesFactory::SERVICE_TAG);

$container->autowire(AVK1ApplicationStatusDeterminer::class)
  ->addTag(AVK1ApplicationStatusDeterminer::SERVICE_TAG);
$container->autowire(AVK1ApplicationActionStatusInfo::class)
  ->addTag(AVK1ApplicationActionStatusInfo::SERVICE_TAG);

$container->autowire(AVK1CaseActionsDeterminer::class)
  ->addTag(AVK1CaseActionsDeterminer::SERVICE_TAG);
