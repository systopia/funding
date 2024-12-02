<?php

/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\DependencyInjection\Util;

use Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface;
use Civi\Funding\Task\Creator\ClearingProcessTaskCreatorInterface;
use Civi\Funding\Task\Creator\DrawdownTaskCreatorInterface;
use Civi\Funding\Task\Creator\FundingCaseTaskCreatorInterface;
use Civi\Funding\Task\Modifier\ApplicationProcessTaskModifierInterface;
use Civi\Funding\Task\Modifier\ClearingProcessTaskModifierInterface;
use Civi\Funding\Task\Modifier\DrawdownTaskModifierInterface;
use Civi\Funding\Task\Modifier\FundingCaseTaskModifierInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @codeCoverageIgnore
 */
final class TaskServiceRegistrator {

  public static function autowireAll(
    ContainerBuilder $container,
    string $dir,
    string $namespace
  ): void {
    ServiceRegistrator::autowireAllImplementing(
      $container,
      $dir,
      $namespace,
      ApplicationProcessTaskCreatorInterface::class,
      [ApplicationProcessTaskCreatorInterface::class => []],
    );

    ServiceRegistrator::autowireAllImplementing(
      $container,
      $dir,
      $namespace,
      ApplicationProcessTaskModifierInterface::class,
      [ApplicationProcessTaskModifierInterface::class => []]
    );

    ServiceRegistrator::autowireAllImplementing(
      $container,
      $dir,
      $namespace,
      ClearingProcessTaskCreatorInterface::class,
      [ClearingProcessTaskCreatorInterface::class => []],
    );

    ServiceRegistrator::autowireAllImplementing(
      $container,
      $dir,
      $namespace,
      ClearingProcessTaskModifierInterface::class,
      [ClearingProcessTaskModifierInterface::class => []],
    );

    ServiceRegistrator::autowireAllImplementing(
      $container,
      $dir,
      $namespace,
      DrawdownTaskCreatorInterface::class,
      [DrawdownTaskCreatorInterface::class => []]
    );

    ServiceRegistrator::autowireAllImplementing(
      $container,
      $dir,
      $namespace,
      DrawdownTaskModifierInterface::class,
      [DrawdownTaskModifierInterface::class => []]
    );

    ServiceRegistrator::autowireAllImplementing(
      $container,
      $dir,
      $namespace,
      FundingCaseTaskCreatorInterface::class,
      [FundingCaseTaskCreatorInterface::class => []],
    );

    ServiceRegistrator::autowireAllImplementing(
      $container,
      $dir,
      $namespace,
      FundingCaseTaskModifierInterface::class,
      [FundingCaseTaskModifierInterface::class => []],
    );
  }

}
