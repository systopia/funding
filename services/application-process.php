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
use Civi\Funding\Api4\Action\FundingApplicationProcess\GetFormDataAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\GetJsonSchemaAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\SaveAction;
use Civi\Funding\Api4\Action\FundingApplicationProcess\UpdateAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateFormAction;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\DefaultApplicationProcessActionsDeterminer;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ReworkPossibleApplicationProcessActionsDeterminer;
use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGenerator;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGeneratorInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationJsonSchemaGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormDataGetHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormNewCreateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormNewSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormNewValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormSubmitHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationFormValidateHandler;
use Civi\Funding\ApplicationProcess\Handler\DefaultApplicationJsonSchemaGetHandler;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ApplicationProcessStatusDeterminerInterface;
use Civi\Funding\ApplicationProcess\StatusDeterminer\DefaultApplicationProcessStatusDeterminer;
use Civi\Funding\ApplicationProcess\StatusDeterminer\ReworkPossibleApplicationProcessStatusDeterminer;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessCreatedSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessIdentifierSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessModificationDateSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessPreDeleteSubscriber;
use Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessStatusSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\ApplicationProcessGetFieldsSubscriber;

$container->autowire(ApplicationProcessManager::class);
$container->autowire(ApplicationCostItemManager::class);
$container->autowire(ApplicationResourcesItemManager::class);
$container->autowire(ApplicationIdentifierGeneratorInterface::class, ApplicationIdentifierGenerator::class);
$container->autowire(ApplicationProcessActivityManager::class);

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

$container->autowire(CreateAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(DeleteAction::class)
  ->setPublic(TRUE)
  ->setShared(TRUE);
$container->autowire(GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(SaveAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(UpdateAction::class)
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
  ->addTag('kernel.event_subscriber');
$container->autowire(ApplicationProcessDAOGetSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(ApplicationProcessActionsDeterminerInterface::class,
  DefaultApplicationProcessActionsDeterminer::class);
$container->autowire(ApplicationProcessStatusDeterminerInterface::class,
  DefaultApplicationProcessStatusDeterminer::class);

$container->autowire(ReworkPossibleApplicationProcessActionsDeterminer::class);
$container->autowire(ReworkPossibleApplicationProcessStatusDeterminer::class);
