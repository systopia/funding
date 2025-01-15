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

use Civi\Api4\Generic\AbstractAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewApplicationFormAction;
use Civi\Funding\DependencyInjection\Compiler\FundingCaseNotificationContactsSetHandlerPass;
use Civi\Funding\DependencyInjection\Compiler\FundingCaseRecipientContactSetHandlerPass;
use Civi\Funding\DependencyInjection\PossibleRecipientsForChangeLoaderPass;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\FundingCase\FundingCaseIdentifierGenerator;
use Civi\Funding\FundingCase\FundingCaseIdentifierGeneratorInterface;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\FundingCasePermissionsCacheManager;
use Civi\Funding\FundingCase\FundingCasePermissionsInitializer;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseApproveHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseFormDataGetHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseFormNewGetHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseFormNewSubmitHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseFormNewValidateHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseFormUpdateGetHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseFormUpdateSubmitHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseFormUpdateValidateHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCasePossibleActionsGetHandler;
use Civi\Funding\FundingCase\Handler\DefaultFundingCaseUpdateAmountApprovedHandler;
use Civi\Funding\FundingCase\Handler\DefaultTransferContractRecreateHandler;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormDataGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewSubmitHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewValidateHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateSubmitHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface;
use Civi\Funding\FundingCase\Recipients\FallbackPossibleRecipientsForChangeLoader;
use Civi\Funding\FundingCase\TransferContractRouter;
use Civi\Funding\Permission\FundingCase\ContactsWithPermissionLoader;
use Civi\Funding\Permission\FundingCase\FundingCaseContactsLoader;
use Civi\Funding\Permission\FundingCase\FundingCaseContactsLoaderCollection;
use Civi\Funding\Permission\FundingCase\FundingCaseContactsLoaderInterface;
use Civi\Funding\Permission\FundingCase\RelationFactory\FundingCaseContactRelationFactory;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryInterface;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryLocator;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryTypeContainer;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryTypeInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

$container->addCompilerPass(new PossibleRecipientsForChangeLoaderPass());
$container->addCompilerPass(new FundingCaseRecipientContactSetHandlerPass());
$container->addCompilerPass(new FundingCaseNotificationContactsSetHandlerPass());

$container->autowire(FundingCaseManager::class)
  // phpcs:disable Squiz.PHP.CommentedOutCode.Found
  // Accessed in \Civi\Funding\Api4\Action\FundingCase\AbstractReferencingDAOGetAction.
  // phpcs:enable
  ->setPublic(TRUE);
$container->autowire(FundingCasePermissionsInitializer::class);
$container->autowire(FundingCasePermissionsCacheManager::class)
  // phpcs:disable Squiz.PHP.CommentedOutCode.Found
  // Used in class \Civi\Funding\Api4\Action\FundingCase\GetAction.
  // phpcs:enable
  ->setPublic(TRUE);
$container->autowire(TransferContractRouter::class)
  // phpcs:disable Squiz.PHP.CommentedOutCode.Found
  // Used in class \Civi\Funding\Api4\Action\FundingCase\GetAction.
  // phpcs:enable
  ->setPublic(TRUE);
$container->autowire(FundingCaseIdentifierGeneratorInterface::class, FundingCaseIdentifierGenerator::class);
$container->autowire(FallbackPossibleRecipientsForChangeLoader::class);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCase/Api4/ActionHandler',
  'Civi\\Funding\\FundingCase\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/FundingCase/Remote/Api4/ActionHandler',
  'Civi\\Funding\\FundingCase\\Remote\\Api4\\ActionHandler',
  ActionHandlerInterface::class,
  [ActionHandlerInterface::SERVICE_TAG => []],
);

$container->autowire(FundingCaseFormNewGetHandlerInterface::class, DefaultFundingCaseFormNewGetHandler::class);
$container->autowire(FundingCaseFormNewSubmitHandlerInterface::class, DefaultFundingCaseFormNewSubmitHandler::class);
$container->autowire(
  FundingCaseFormNewValidateHandlerInterface::class,
  DefaultFundingCaseFormNewValidateHandler::class
);

$container->autowire(FundingCaseFormDataGetHandlerInterface::class, DefaultFundingCaseFormDataGetHandler::class);
$container->autowire(FundingCaseFormUpdateGetHandlerInterface::class, DefaultFundingCaseFormUpdateGetHandler::class);
$container->autowire(
  FundingCaseFormUpdateSubmitHandlerInterface::class,
  DefaultFundingCaseFormUpdateSubmitHandler::class
);
$container->autowire(
  FundingCaseFormUpdateValidateHandlerInterface::class,
  DefaultFundingCaseFormUpdateValidateHandler::class
);

$container->autowire(FundingCaseApproveHandlerInterface::class, DefaultFundingCaseApproveHandler::class);
$container->autowire(
  FundingCasePossibleActionsGetHandlerInterface::class,
  DefaultFundingCasePossibleActionsGetHandler::class
);
$container->autowire(
  FundingCaseUpdateAmountApprovedHandlerInterface::class,
  DefaultFundingCaseUpdateAmountApprovedHandler::class
);
$container->autowire(TransferContractRecreateHandlerInterface::class, DefaultTransferContractRecreateHandler::class);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Api4/Action/FundingCase',
  'Civi\\Funding\\Api4\\Action\\FundingCase',
  AbstractAction::class,
  [],
  [
    'public' => TRUE,
    'shared' => FALSE,
  ]
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Api4/Action/FundingCaseContactRelation',
  'Civi\\Funding\\Api4\\Action\\FundingCaseContactRelation',
  AbstractAction::class,
  [],
  [
    'public' => TRUE,
    'shared' => FALSE,
  ]
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Api4/Action/FundingCaseContactRelationPropertiesFactoryType',
  'Civi\\Funding\\Api4\\Action\\FundingCaseContactRelationPropertiesFactoryType',
  AbstractAction::class,
  [],
  [
    'public' => TRUE,
    'shared' => FALSE,
  ]
);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Api4/Action/FundingNewCasePermissions',
  'Civi\\Funding\\Api4\\Action\\FundingNewCasePermissions',
  AbstractAction::class,
  [],
  [
    'public' => TRUE,
    'shared' => FALSE,
  ]
);

$container->autowire(FundingCaseContactRelationFactory::class);

$container->autowire(RelationPropertiesFactoryLocator::class)
  ->addArgument(new ServiceLocatorArgument(
    new TaggedIteratorArgument('funding.case.contact_relation_properties_factory', NULL, 'getSupportedFactoryType')
  ));

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Permission/FundingCase/RelationFactory/Types',
  'Civi\\Funding\\Permission\\FundingCase\\RelationFactory\\Types',
  RelationPropertiesFactoryTypeInterface::class,
  ['funding.case.contact_relation_properties_factory_type' => []],
);

$container->autowire(RelationPropertiesFactoryTypeContainer::class)
  ->addArgument(new TaggedIteratorArgument('funding.case.contact_relation_properties_factory_type'));

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Permission/FundingCase/RelationFactory/Factory',
  'Civi\\Funding\\Permission\\FundingCase\\RelationFactory\\Factory',
  RelationPropertiesFactoryInterface::class,
  ['funding.case.contact_relation_properties_factory' => []],
);

$container->register(FundingCaseContactsLoaderInterface::class, FundingCaseContactsLoaderCollection::class)
  ->addArgument(new TaggedIteratorArgument('funding.case.contacts_loader'));
$container->autowire(FundingCaseContactsLoader::class)
  ->addTag('funding.case.contacts_loader');
$container->autowire(ContactsWithPermissionLoader::class);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/EventSubscriber/FundingCase',
  'Civi\\Funding\\EventSubscriber\\FundingCase',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => 'auto'],
);

$container->autowire(GetNewApplicationFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(SubmitNewApplicationFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(ValidateNewApplicationFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
