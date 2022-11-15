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
use Civi\Funding\Form\ApplicationSubmitActionsFactory;
use Civi\Funding\Form\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\Form\ReworkPossibleApplicationSubmitActionsContainerFactory;
use Civi\Funding\Form\SubmitActionsContainer;
use Civi\Funding\Form\Validation\FormValidator;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\OpisValidatorFactory;
use Civi\Funding\Form\Validation\Validator;
use Civi\Funding\Form\Validation\ValidatorInterface;
use Opis\JsonSchema\Validator as OpisValidator;
use Symfony\Component\DependencyInjection\Reference;

$container->register(OpisValidator::class)->setFactory([OpisValidatorFactory::class, 'getValidator']);
$container->autowire(ValidatorInterface::class, Validator::class);
$container->autowire(FormValidatorInterface::class, FormValidator::class);

$container->register('funding.application.submit_actions_container', SubmitActionsContainer::class)
  ->setFactory([ReworkPossibleApplicationSubmitActionsContainerFactory::class, 'create']);
$container->autowire(ApplicationSubmitActionsFactoryInterface::class, ApplicationSubmitActionsFactory::class)
  ->setArgument('$submitActionsContainer', new Reference('funding.application.submit_actions_container'));

$container->autowire(GetApplicationFormSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(ValidateApplicationFormSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
$container->autowire(SubmitApplicationFormSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);
