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

use Civi\Funding\Api4\Action\FundingCase\GetAction;
use Civi\Funding\Api4\Action\FundingCase\GetFieldsAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewApplicationFormAction;
use Civi\Funding\EventSubscriber\FundingCase\AddFundingCasePermissionsSubscriber;
use Civi\Funding\EventSubscriber\FundingCase\ApplicationProcessDeletedSubscriber;
use Civi\Funding\EventSubscriber\FundingCase\ApplicationProcessStatusSubscriber;
use Civi\Funding\EventSubscriber\FundingCase\FundingCaseFilterPermissionsSubscriber;
use Civi\Funding\EventSubscriber\FundingCase\FundingCaseGetPossiblePermissionsSubscriber;
use Civi\Funding\EventSubscriber\FundingCase\FundingCasePermissionsGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingCaseGetFieldsSubscriber;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\FundingCaseStatusDeterminer;
use Civi\Funding\FundingCase\FundingCaseStatusDeterminerInterface;
use Civi\Funding\Permission\FundingCase\RelationFactory\FundingCaseContactRelationFactory;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryLocator;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryTypeContainer;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

$container->autowire(FundingCaseManager::class);
$container->autowire(FundingCaseStatusDeterminerInterface::class, FundingCaseStatusDeterminer::class);

$container->autowire(GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(\Civi\Funding\Api4\Action\FundingCaseContactRelation\GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(Civi\Funding\Api4\Action\FundingCaseContactRelationPropertiesFactoryType\GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(Civi\Funding\Api4\Action\FundingCaseContactRelationPropertiesFactoryType\GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(\Civi\Funding\Api4\Action\FundingNewCasePermissions\GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(FundingCaseContactRelationFactory::class);

$container->autowire(RelationPropertiesFactoryLocator::class)
  ->addArgument(new ServiceLocatorArgument(
    new TaggedIteratorArgument('funding.case.contact_relation_properties_factory', NULL, 'getSupportedFactoryType')
  ));

// @phpstan-ignore-next-line
foreach (glob(__DIR__ . '/../Civi/Funding/Permission/FundingCase/RelationFactory/Types/*.php') as $file) {
  $class = basename($file, '.php');
  $container->autowire('Civi\\Funding\\Permission\\FundingCase\\RelationFactory\\Types\\' . $class)
    ->addTag('funding.case.contact_relation_properties_factory_type');
}

$container->autowire(RelationPropertiesFactoryTypeContainer::class)
  ->addArgument(new TaggedIteratorArgument('funding.case.contact_relation_properties_factory_type'));

// @phpstan-ignore-next-line
foreach (glob(__DIR__ . '/../Civi/Funding/Permission/FundingCase/RelationFactory/Factory/*.php') as $file) {
  $class = basename($file, '.php');
  $container->autowire('Civi\\Funding\\Permission\\FundingCase\\RelationFactory\\Factory\\' . $class)
    ->addTag('funding.case.contact_relation_properties_factory');
}

$container->autowire(FundingCaseGetFieldsSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingCaseDAOGetSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingCasePermissionsGetSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingCaseFilterPermissionsSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingCaseGetPossiblePermissionsSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(AddFundingCasePermissionsSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(ApplicationProcessStatusSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessDeletedSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);

$container->autowire(GetNewApplicationFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(SubmitNewApplicationFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(ValidateNewApplicationFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
