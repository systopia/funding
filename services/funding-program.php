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

use Civi\Funding\Api4\Action\FundingProgram\GetAction;
use Civi\Funding\Api4\Action\FundingProgram\GetFieldsAction;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\EventSubscriber\FundingProgram\FundingProgramFilterPermissionsSubscriber;
use Civi\Funding\EventSubscriber\FundingProgram\FundingProgramGetPossiblePermissionsSubscriber;
use Civi\Funding\EventSubscriber\FundingProgram\FundingProgramPermissionsGetAdminSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramGetFieldsSubscriber;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

$container->autowire(FundingProgramManager::class);
$container->autowire(FundingCaseTypeProgramRelationChecker::class);
$container->autowire(FundingCaseTypeManager::class);

$container->autowire(GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(\Civi\Funding\Api4\Action\FundingProgramContactRelation\GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/FundingProgram/Api4/ActionHandler',
  'Civi\\Funding\\FundingProgram\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

$container->autowire(FundingProgramGetFieldsSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingProgramDAOGetSubscriber::class)
  ->addTag('kernel.event_subscriber');

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/EventSubscriber/FundingProgram',
  'Civi\\Funding\\EventSubscriber\\FundingProgram',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => TRUE],
);

/*
 * Subscriber services are created every time (even when not used), so in
 * general only those subscribers that do not depend on any other service or
 * only on services that are created anyway or are cheap to create should not be
 * lazy.
 */
$nonLazySubscribers = [
  FundingProgramFilterPermissionsSubscriber::class,
  FundingProgramGetPossiblePermissionsSubscriber::class,
  FundingProgramPermissionsGetAdminSubscriber::class,
];
foreach ($nonLazySubscribers as $serviceId) {
  $container->getDefinition($serviceId)->setLazy(FALSE);
}
