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

namespace Civi\Funding\DependencyInjection\Compiler\Traits;

use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

trait TaggedFundingCaseTypeServicesTrait {

  /**
   * @phpstan-var list<string>
   */
  protected static array $fundingCaseTypes = [];

  /**
   * @phpstan-return array<string, Reference>
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   */
  protected function getTaggedFundingCaseTypeServices(ContainerBuilder $container, string $tagName): array {
    $services = [];
    foreach ($container->findTaggedServiceIds($tagName) as $id => $tags) {
      foreach ($tags as $attributes) {
        foreach ($this->getFundingCaseTypes($container, $id, $attributes) as $fundingCaseType) {
          if (isset($services[$fundingCaseType])) {
            throw new RuntimeException(
              sprintf(
                'Duplicate service with tag "%s" and funding case type "%s" (IDs: %s, %s)',
                $tagName,
                $fundingCaseType,
                (string) $services[$fundingCaseType],
                $id,
              )
            );
          }
          $services[$fundingCaseType] = new Reference($id);
          if (!in_array($fundingCaseType, static::$fundingCaseTypes, TRUE)) {
            static::$fundingCaseTypes[] = $fundingCaseType;
          }
        }
      }
    }

    return $services;
  }

  /**
   * @phpstan-return array<string, IteratorArgument>
   *   Mapping of funding case type to prioritized service references.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   */
  protected function getMultiTaggedFundingCaseTypeServices(ContainerBuilder $container, string $tagName): array {
    /** @phpstan-var array<string, \SplPriorityQueue<int, Reference>> $services */
    $services = [];
    foreach ($container->findTaggedServiceIds($tagName) as $id => $tags) {
      foreach ($tags as $attributes) {
        foreach ($this->getFundingCaseTypes($container, $id, $attributes) as $fundingCaseType) {
          if (!isset($services[$fundingCaseType])) {
            $services[$fundingCaseType] = new \SplPriorityQueue();
          }

          $services[$fundingCaseType]->insert(new Reference($id), $this->getPriority($container, $id, $attributes));

          if (!in_array($fundingCaseType, static::$fundingCaseTypes, TRUE)) {
            static::$fundingCaseTypes[] = $fundingCaseType;
          }
        }
      }
    }

    return array_map(
      fn (\SplPriorityQueue $priorityQueue) => new IteratorArgument(iterator_to_array($priorityQueue)),
      $services
    );
  }

  /**
   * @phpstan-param array{funding_case_type?: string, funding_case_types?: list<string>} $attributes
   *
   * @phpstan-return list<string>
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   */
  protected function getFundingCaseTypes(ContainerBuilder $container, string $id, array $attributes): array {
    if (array_key_exists('funding_case_types', $attributes)) {
      return $attributes['funding_case_types'];
    }

    if (array_key_exists('funding_case_type', $attributes)) {
      return [$attributes['funding_case_type']];
    }

    $class = $this->getServiceClass($container, $id);
    if (method_exists($class, 'getSupportedFundingCaseTypes')) {
      /** @phpstan-var list<string> $fundingCaseTypes */
      $fundingCaseTypes = $class::getSupportedFundingCaseTypes();

      return $fundingCaseTypes;
    }

    if (!method_exists($class, 'getSupportedFundingCaseType')) {
      throw new RuntimeException(sprintf('No funding case type specified for service "%s"', $id));
    }

    /** @var string $fundingCaseType */
    $fundingCaseType = $class::getSupportedFundingCaseType();

    return [$fundingCaseType];
  }

  /**
   * @phpstan-param array{priority?: int} $attributes
   */
  private function getPriority(ContainerBuilder $container, string $id, array $attributes): int {
    if (isset($attributes['priority'])) {
      return $attributes['priority'];
    }

    $class = $this->getServiceClass($container, $id);
    if (method_exists($class, 'getPriority')) {
      // @phpstan-ignore return.type
      return $class::getPriority();
    }

    $constName = $class . '::PRIORITY';
    if (defined($constName)) {
      // @phpstan-ignore return.type
      return constant($constName);
    }

    return 0;
  }

  /**
   * @phpstan-return class-string
   */
  private function getServiceClass(ContainerBuilder $container, string $id): string {
    $definition = $container->getDefinition($id);

    /** @phpstan-var class-string $class */
    $class = $definition->getClass() ?? $id;

    return $class;
  }

}
