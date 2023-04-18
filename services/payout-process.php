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
use Civi\Funding\EventSubscriber\Remote\Drawdown\DrawdownCreateSubscriber;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Validation\ConcreteEntityValidatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

$container->autowire(DrawdownManager::class);
$container->autowire(PayoutProcessManager::class);

$container->autowire(DrawdownCreateSubscriber::class)
  ->addTag('kernel.event_subscriber');

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Api4/Action/FundingPayoutProcess',
  'Civi\\Funding\\Api4\\Action\\FundingPayoutProcess',
  AbstractAction::class,
  [],
  [
    'public' => TRUE,
    'shared' => FALSE,
  ]
);

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
  __DIR__ . '/../Civi/Funding/EventSubscriber/Remote/Drawdown',
  'Civi\\Funding\\EventSubscriber\\Remote\\Drawdown',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => TRUE],
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/EventSubscriber/Remote/PayoutProcess',
  'Civi\\Funding\\EventSubscriber\\Remote\\PayoutProcess',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => TRUE],
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/PayoutProcess/Validator',
  'Civi\\Funding\\PayoutProcess\\Validator',
  ConcreteEntityValidatorInterface::class,
  ['funding.validator.entity' => []]
);
