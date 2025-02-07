<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\DependencyInjection\Compiler;

use Civi\Funding\DependencyInjection\Compiler\Traits\TaggedFundingCaseTypeServicesTrait;
use Civi\Funding\Task\Creator\ApplicationProcessTaskCreatorInterface;
use Civi\Funding\Task\Creator\ClearingProcessTaskCreatorInterface;
use Civi\Funding\Task\Creator\DrawdownTaskCreatorInterface;
use Civi\Funding\Task\Creator\FundingCaseTaskCreatorInterface;
use Civi\Funding\Task\EventSubscriber\ApplicationProcessTaskSubscriber;
use Civi\Funding\Task\EventSubscriber\ClearingProcessTaskSubscriber;
use Civi\Funding\Task\EventSubscriber\DrawdownTaskSubscriber;
use Civi\Funding\Task\EventSubscriber\FundingCaseTaskSubscriber;
use Civi\Funding\Task\Modifier\ApplicationProcessTaskModifierInterface;
use Civi\Funding\Task\Modifier\ClearingProcessTaskModifierInterface;
use Civi\Funding\Task\Modifier\DrawdownTaskModifierInterface;
use Civi\Funding\Task\Modifier\FundingCaseTaskModifierInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @codeCoverageIgnore
 */
final class FundingTaskPass implements CompilerPassInterface {

  use TaggedFundingCaseTypeServicesTrait;

  /**
   * @inheritDoc
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   */
  public function process(ContainerBuilder $container): void {
    foreach ($this->getSubscriberSpecifications() as $subscriberClass => [$creatorTag, $modifierTag]) {
      $taskCreatorServices = $this->getMultiTaggedFundingCaseTypeServices($container, $creatorTag);
      $taskModifierServices = $this->getMultiTaggedFundingCaseTypeServices($container, $modifierTag);

      $container->autowire($subscriberClass)
        ->setClass($subscriberClass)
        // Needs to be public for not being removed in optimization step.
        ->setPublic(TRUE)
        ->setArgument('$taskCreators', $taskCreatorServices)
        ->setArgument('$taskModifiers', $taskModifierServices)
        ->addTag('event_subscriber')
        ->setLazy(!(new \ReflectionClass($subscriberClass))->isFinal());
    }

  }

  /**
   * @phpstan-return iterable<class-string, array{string, string}>
   */
  private function getSubscriberSpecifications(): iterable {
    yield FundingCaseTaskSubscriber::class => [
      FundingCaseTaskCreatorInterface::class,
      FundingCaseTaskModifierInterface::class,
    ];

    yield ApplicationProcessTaskSubscriber::class => [
      ApplicationProcessTaskCreatorInterface::class,
      ApplicationProcessTaskModifierInterface::class,
    ];

    yield ClearingProcessTaskSubscriber::class => [
      ClearingProcessTaskCreatorInterface::class,
      ClearingProcessTaskModifierInterface::class,
    ];

    yield DrawdownTaskSubscriber::class => [
      DrawdownTaskCreatorInterface::class,
      DrawdownTaskModifierInterface::class,
    ];
  }

}
