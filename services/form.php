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

use Civi\Funding\EventSubscriber\Form\GetApplicationFormSubscriber;
use Civi\Funding\EventSubscriber\Form\SubmitApplicationFormSubscriber;
use Civi\Funding\EventSubscriber\Form\ValidateApplicationFormSubscriber;
use Civi\Funding\Form\ApplicationFormFactoryCollection;
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Form\Handler\GetApplicationFormHandler;
use Civi\Funding\Form\Handler\GetApplicationFormHandlerInterface;
use Civi\Funding\Form\Handler\SubmitApplicationFormHandler;
use Civi\Funding\Form\Handler\SubmitApplicationFormHandlerInterface;
use Civi\Funding\Form\Handler\ValidateApplicationFormHandler;
use Civi\Funding\Form\Handler\ValidateApplicationFormHandlerInterface;
use Civi\Funding\Form\Validation\FormValidator;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\OpisValidatorFactory;
use Opis\JsonSchema\Validator;
use Symfony\Component\DependencyInjection\Argument\ServiceLocatorArgument;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;

$container->register(Validator::class)->setFactory([OpisValidatorFactory::class, 'getValidator']);
$container->autowire(FormValidatorInterface::class, FormValidator::class);

$container->register(ApplicationFormFactoryInterface::class, ApplicationFormFactoryCollection::class)
  ->addArgument(new ServiceLocatorArgument(
    new TaggedIteratorArgument('funding.form_factory', 'funding_case_type', 'getSupportedFundingCaseType', TRUE)
  ));

$container->autowire(GetApplicationFormHandlerInterface::class, GetApplicationFormHandler::class);
$container->autowire(ValidateApplicationFormHandlerInterface::class, ValidateApplicationFormHandler::class);
$container->autowire(SubmitApplicationFormHandlerInterface::class, SubmitApplicationFormHandler::class);

$container->autowire(GetApplicationFormSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ValidateApplicationFormSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(SubmitApplicationFormSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
