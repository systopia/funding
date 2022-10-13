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
use Civi\Funding\EventSubscriber\FundingProgram\FundingProgramGetPossiblePermissionsSubscriber;
use Civi\Funding\EventSubscriber\FundingProgram\FundingProgramPermissionsGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramDAOGetSubscriber;
use Civi\Funding\EventSubscriber\Remote\FundingProgramGetFieldsSubscriber;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;

$container->autowire(FundingProgramManager::class);
$container->autowire(FundingCaseTypeProgramRelationChecker::class);
$container->autowire(FundingCaseTypeManager::class);

$container->autowire(GetAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);
$container->autowire(GetFieldsAction::class)
  ->setPublic(TRUE)
  ->setShared(FALSE);

$container->autowire(FundingProgramGetFieldsSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingProgramDAOGetSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingProgramPermissionsGetSubscriber::class)
  ->addTag('kernel.event_subscriber');
$container->autowire(FundingProgramGetPossiblePermissionsSubscriber::class)
  ->addTag('kernel.event_subscriber');
