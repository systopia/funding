<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

use Civi\Api4\Generic\AbstractAction;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\PayoutProcess\BankAccountManager;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\Handler\DrawdownDocumentRenderHandler;
use Civi\Funding\PayoutProcess\Handler\DrawdownDocumentRenderHandlerInterface;
use Civi\Funding\PayoutProcess\DrawdownDocumentCreator;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\PayoutProcess\Token\DrawdownTokenNameExtractor;
use Civi\Funding\PayoutProcess\Token\DrawdownTokenResolver;
use Civi\Funding\Validation\ConcreteEntityValidatorInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

$container->autowire(DrawdownManager::class);
$container->autowire(PayoutProcessManager::class);
$container->autowire(BankAccountManager::class);

$container->autowire(DrawdownDocumentCreator::class);
$container->autowire(DrawdownDocumentRenderHandlerInterface::class, DrawdownDocumentRenderHandler::class);

$container->autowire(DrawdownTokenNameExtractor::class);
$container->autowire(DrawdownTokenResolver::class);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Api4/Action/FundingDrawdown',
  'Civi\\Funding\\Api4\\Action\\FundingDrawdown',
  AbstractAction::class,
  [],
  [
    'public' => TRUE,
    'shared' => FALSE,
  ]
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/EventSubscriber/PayoutProcess',
  'Civi\\Funding\\EventSubscriber\\PayoutProcess',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => TRUE],
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/PayoutProcess/Api4/ActionHandler',
  'Civi\\Funding\\PayoutProcess\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/PayoutProcess/Validator',
  'Civi\\Funding\\PayoutProcess\\Validator',
  ConcreteEntityValidatorInterface::class,
  ['funding.validator.entity' => []]
);
