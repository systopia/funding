<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

// phpcs:disable Drupal.Commenting.DocComment.ContentAfterOpen
/** @var \Symfony\Component\DependencyInjection\ContainerBuilder $container */

use Civi\Funding\Translation\Api4\ActionHandler\ExtractStringsActionHandler;
use Civi\Funding\Translation\EventSubscriber\ExtractStringsSearchKitTaskSubscriber;
use Civi\Funding\Translation\EventSubscriber\TranslationStringPreUpdateSubscriber;
use Civi\Funding\Translation\FormStringTranslationLoader;
use Civi\Funding\Translation\FormStringTranslationUpdater;
use Civi\Funding\Translation\FormTranslator;
use Civi\Funding\Translation\FormTranslatorInterface;
use Civi\Funding\Translation\JsonSchemaStringExtractor;
use Civi\Funding\Translation\JsonSchemaStringTranslator;
use Civi\Funding\Translation\StringExtractor;
use Civi\Funding\Translation\UiSchemaStringExtractor;
use Civi\Funding\Translation\UiSchemaStringTranslator;

$container->autowire(ExtractStringsActionHandler::class)
  ->addTag(ExtractStringsActionHandler::SERVICE_TAG);

$container->autowire(FormStringTranslationUpdater::class);
$container->autowire(StringExtractor::class);
$container->autowire(JsonSchemaStringExtractor::class);
$container->autowire(UiSchemaStringExtractor::class);

$container->autowire(FormTranslatorInterface::class, FormTranslator::class);
$container->autowire(JsonSchemaStringTranslator::class);
$container->autowire(UiSchemaStringTranslator::class);
$container->autowire(FormStringTranslationLoader::class);

$container->autowire(ExtractStringsSearchKitTaskSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(TranslationStringPreUpdateSubscriber::class)
  ->addTag('kernel.event_subscriber');
