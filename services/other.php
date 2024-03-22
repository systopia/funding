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

use Civi\Funding\Api4\DAOActionFactory;
use Civi\Funding\Api4\DAOActionFactoryInterface;
use Civi\Funding\Contact\FundingRemoteContactIdResolver;
use Civi\Funding\Contact\FundingRemoteContactIdResolverInterface;
use Civi\Funding\Controller\PageControllerInterface;
use Civi\Funding\ControllerDectorator\TransactionalPageController;
use Civi\Funding\Database\ChangeSetFactory;
use Civi\Funding\Database\DaoEntityInfoProvider;
use Civi\Funding\DependencyInjection\Compiler\ActionPropertyAutowireFixPass;
use Civi\Funding\DependencyInjection\Compiler\EntityValidatorPass;
use Civi\Funding\DependencyInjection\Compiler\FundingCaseTypeServiceLocatorPass;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\EventSubscriber\Api\TransactionalApiRequestSubscriber;
use Civi\Funding\EventSubscriber\FundingFilterPossiblePermissionsSubscriber;
use Civi\Funding\EventSubscriber\FundingCiviOfficeSearchKitTaskSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingRequestInitSubscriber;
use Civi\Funding\EventSubscriber\Remote\RemotePageRequestSubscriber;
use Civi\Funding\FundingAttachmentManager;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\FundingExternalFileManager;
use Civi\Funding\FundingExternalFileManagerInterface;
use Civi\Funding\Util\MoneyFactory;
use Civi\Funding\Util\UrlGenerator;
use Civi\Funding\Validation\EntityValidator;
use Civi\Funding\Validation\EntityValidatorInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

if (!$container->has(PropertyAccessorInterface::class)) {
  $container->register(PropertyAccessorInterface::class, PropertyAccess::class)
    ->setFactory([PropertyAccess::class, 'createPropertyAccessor']);
}

$container->autowire(DAOActionFactoryInterface::class, DAOActionFactory::class);

$container->autowire(UrlGenerator::class);
$container->autowire(MoneyFactory::class);
$container->autowire(ChangeSetFactory::class);
$container->autowire(DaoEntityInfoProvider::class);

$container->addCompilerPass(new ActionPropertyAutowireFixPass(), PassConfig::TYPE_BEFORE_REMOVING);
$container->addCompilerPass(new FundingCaseTypeServiceLocatorPass());

$container->autowire(FundingRemoteContactIdResolverInterface::class, FundingRemoteContactIdResolver::class);

$container->autowire(FundingRequestInitSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);

$container->autowire(RemotePageRequestSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(TransactionalApiRequestSubscriber::class, TransactionalApiRequestSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(FundingFilterPossiblePermissionsSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(FundingCiviOfficeSearchKitTaskSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(EntityValidatorInterface::class, EntityValidator::class);
$container->addCompilerPass(new EntityValidatorPass());

$container->autowire(FundingAttachmentManagerInterface::class, FundingAttachmentManager::class);
$container->autowire(FundingExternalFileManagerInterface::class, FundingExternalFileManager::class);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/EventSubscriber/ExternalFile',
  'Civi\\Funding\\EventSubscriber\\ExternalFile',
  EventSubscriberInterface::class,
  ['kernel.event_subscriber' => []],
  ['lazy' => 'auto'],
);

$controllerDefinitions = ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/Controller',
  'Civi\\Funding\\Controller',
  PageControllerInterface::class,
  [],
  ['public' => TRUE],
);

// Make controllers run in database transaction.
foreach (array_keys($controllerDefinitions) as $serviceId) {
  $container->autowire($serviceId . '.transactional', TransactionalPageController::class)
    ->setDecoratedService($serviceId)
    ->setArgument('$controller', new Reference($serviceId . '.transactional.inner'));
}
