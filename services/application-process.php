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

use Civi\Funding\Api4\Action\FundingApplicationProcess\CreateAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\DeleteAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\GetAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\GetFieldsAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\GetFormDataAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\GetJsonSchemaAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\SaveAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\UpdateAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateFormAction;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\DefaultApplicationProcessActionsDeterminer;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ReworkPossibleApplicationProcessActionsDeterminer;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\DefaultApplicationProcessActionStatusInfo;
use Civi\Funding\ApplicationProcess\ActionStatusInfo\ReworkPossibleApplicationProcessActionStatusInfo;
use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGenerator;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGeneratorInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessTaskManager;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationJsonSchemaGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationCostItemsAddIdentifiersHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationCostItemsPersistHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationDeleteHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormDataGetHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormNewCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormNewSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormNewValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationJsonSchemaGetHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationResourcesItemsAddIdentifiersHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationResourcesItemsPersistHandler;
use Civi\Funding\ApplicationProcess\StatusDeterminer\DefaultApplicationProcessStatusDeterminer;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ReworkPossibleApplicationProcessStatusDeterminer;
use Civi\Funding\DependencyInjection\Util\ServiceRegistrator;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationCostItemsSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessCreatedSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessIdentifierSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessModificationDateSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessPreDeleteSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessReviewAssignmentSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessReviewStatusSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessReviewTaskSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessReworkTaskSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessStatusSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationResourcesItemsSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessActivityGetFieldsSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessActivityGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessGetFieldsSubscriber;
use Civi\Funding\Validation\ConcreteEntityValidatorInterface;
use Symfony\Component\DependencyInjection\Reference;

$container->autowire(ApplicationProcessManager::class);
$container->autowire(ApplicationProcessBundleLoader::class);
$container->autowire(ApplicationCostItemManager::class);
$container->autowire(ApplicationResourcesItemManager::class);
$container->autowire(ApplicationIdentifierGeneratorInterface::class, ApplicationIdentifierGenerator::class);
$container->autowire(ApplicationProcessActivityManager::class);
$container->autowire(ApplicationProcessTaskManager::class);

ServiceRegistrator::autowireAllImplementing(
  $container,
  __DIR__ . '/../Civi/Funding/ApplicationProcess/Validator',
  'Civi\\Funding\\ApplicationProcess\\Validator',
  ConcreteEntityValidatorInterface::class,
  ['funding.validator.entity' => []]
);

$container->autowire(ApplicationDeleteHandlerInterface::class, DefaultApplicationDeleteHandler::class);

$container->autowire(ApplicationFormNewCreateHandlerInterface::class, DefaultApplicationFormNewCreateHandler::class);
$container->autowire(
  ApplicationFormNewValidateHandlerInterface::class,
  DefaultApplicationFormNewValidateHandler::class
);
$container->autowire(ApplicationFormNewSubmitHandlerInterface::class, DefaultApplicationFormNewSubmitHandler::class);

$container->autowire(ApplicationFormDataGetHandlerInterface::class, DefaultApplicationFormDataGetHandler::class);
$container->autowire(ApplicationFormCreateHandlerInterface::class, DefaultApplicationFormCreateHandler::class);
$container->autowire(ApplicationFormValidateHandlerInterface::class, DefaultApplicationFormValidateHandler::class);
$container->autowire(ApplicationFormSubmitHandlerInterface::class, DefaultApplicationFormSubmitHandler::class);
$container->autowire(ApplicationJsonSchemaGetHandlerInterface::class, DefaultApplicationJsonSchemaGetHandler::class);
$container->autowire(
  ApplicationCostItemsAddIdentifiersHandlerInterface::class,
  DefaultApplicationCostItemsAddIdentifiersHandler::class
);
$container->autowire(
  ApplicationCostItemsPersistHandlerInterface::class,
  DefaultApplicationCostItemsPersistHandler::class
);
$container->autowire(
  ApplicationResourcesItemsAddIdentifiersHandlerInterface::class,
  DefaultApplicationResourcesItemsAddIdentifiersHandler::class
);
$container->autowire(
  ApplicationResourcesItemsPersistHandlerInterface::class,
  DefaultApplicationResourcesItemsPersistHandler::class
);

$container->autowire(CreateAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(DeleteAction::class)
  ->setPublic(TRUE)
  ->setShared(TRUE);
$container->autowire(GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(SaveAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(UpdateAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(\Civi\Funding\Api4\Action\FundingApplicationProcessActivity\GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(GetFormDataAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(\Civi\Funding\Api4\Action\FundingApplicationProcess\SubmitFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(\Civi\Funding\Api4\Action\FundingApplicationProcess\ValidateFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(GetJsonSchemaAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(GetFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(SubmitFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(ValidateFormAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(ApplicationProcessGetFieldsSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(ApplicationProcessIdentifierSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessCreatedSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessPreDeleteSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessModificationDateSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessStatusSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessReviewAssignmentSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessReviewStatusSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessReviewTaskSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessReworkTaskSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationCostItemsSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationResourcesItemsSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ApplicationProcessDAOGetSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(ApplicationProcessActivityGetSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(ApplicationProcessActivityGetFieldsSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(DefaultApplicationProcessActionsDeterminer::class);
$container->autowire(DefaultApplicationProcessStatusDeterminer::class);
$container->autowire(DefaultApplicationProcessActionStatusInfo::class);

$container->autowire(ReworkPossibleApplicationProcessActionsDeterminer::class)
  ->addArgument(new Reference(DefaultApplicationProcessActionsDeterminer::class));
$container->autowire(ReworkPossibleApplicationProcessStatusDeterminer::class)
  ->addArgument(new Reference(DefaultApplicationProcessStatusDeterminer::class));
;
$container->autowire(ReworkPossibleApplicationProcessActionStatusInfo::class)
  ->addArgument(new Reference(DefaultApplicationProcessActionStatusInfo::class));
