<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

use Civi\Funding\Validation\EntityValidatorLoader;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @codeCoverageIgnore
 */
final class EntityValidatorPass implements CompilerPassInterface {

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $validators = [];
    foreach ($container->findTaggedServiceIds('funding.validator.entity') as $id => $tags) {
      foreach ($tags as $attributes) {
        $entityClass = $this->getEntityClass($container, $id, $attributes);
        if (isset($validators[$entityClass])) {
          $validators[$entityClass][] = new Reference($id);
        }
        else {
          $validators[$entityClass] = [new Reference($id)];
        }
      }
    }

    $validatorsArg = array_map(fn(array $refs) => new IteratorArgument($refs), $validators);
    $container->register(EntityValidatorLoader::class, EntityValidatorLoader::class)->addArgument($validatorsArg);
  }

  /**
   * @phpstan-param array{entity-class?: class-string} $attributes
   *
   * @phpstan-return class-string
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\RuntimeException
   */
  private function getEntityClass(ContainerBuilder $container, string $id, array $attributes): string {
    if (array_key_exists('entity_class', $attributes)) {
      return $attributes['entity_class'];
    }

    $class = $this->getServiceClass($container, $id);
    if (!method_exists($class, 'getEntityClass')) {
      throw new RuntimeException(sprintf('No entity class specified for service "%s"', $id));
    }

    /** @phpstan-var class-string $entityClass */
    $entityClass = $class::getEntityClass();

    return $entityClass;
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
