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

use Civi\Funding\Contact\FundingRemoteContactIdResolver;
use Civi\Funding\Contact\FundingRemoteContactIdResolverInterface;
use Civi\Funding\DependencyInjection\Compiler\EntityValidatorPass;
use Civi\Funding\DependencyInjection\Compiler\FundingCaseTypeServiceLocatorPass;
use Civi\Funding\EventSubscriber\Api\TransactionalApiRequestSubscriber;
use Civi\Funding\EventSubscriber\FundingFilterPossiblePermissionsSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingRequestInitSubscriber;
use Civi\Funding\Validation\EntityValidator;
use Civi\Funding\Validation\EntityValidatorInterface;

$container->addCompilerPass(new FundingCaseTypeServiceLocatorPass());

$container->autowire(FundingRemoteContactIdResolverInterface::class, FundingRemoteContactIdResolver::class);

$container->autowire(FundingRequestInitSubscriber::class)
  ->addTag('kernel.event_subscriber')
  ->setLazy(TRUE);

$container->autowire(TransactionalApiRequestSubscriber::class, TransactionalApiRequestSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(FundingFilterPossiblePermissionsSubscriber::class)
  ->addTag('kernel.event_subscriber');

$container->autowire(EntityValidatorInterface::class, EntityValidator::class);
$container->addCompilerPass(new EntityValidatorPass());
