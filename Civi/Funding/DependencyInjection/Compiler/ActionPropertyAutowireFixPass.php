<?php
declare(strict_types = 1);

namespace Civi\Funding\DependencyInjection\Compiler;

use Civi\Api4\Generic\AbstractAction;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Symfony DI tries to autowire properties annotated with @required. Though
 * CiviCRM actions parameters can be annotated in this way to make them
 * required. This pass clears the properties that are going to be injected for
 * action classes registered as service in the Civi\Funding\Api4\Action
 * namespace.
 *
 * @see https://github.com/symfony/symfony/pull/52910
 */
final class ActionPropertyAutowireFixPass implements CompilerPassInterface {

  public function process(ContainerBuilder $container): void {
    foreach ($container->getDefinitions() as $id => $definition) {
      if ([] === $definition->getProperties() || !str_starts_with($id, 'Civi\\Funding\\Api4\\Action\\')) {
        continue;
      }

      if (is_a($id, AbstractAction::class, TRUE)) {
        $definition->setProperties([]);
      }
    }
  }

}
