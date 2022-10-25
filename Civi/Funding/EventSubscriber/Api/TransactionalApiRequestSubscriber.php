<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\Api;

use Civi\RemoteTools\EventSubscriber\AbstractTransactionalApiRequestSubscriber;

/**
 * @codeCoverageIgnore
 */
final class TransactionalApiRequestSubscriber extends AbstractTransactionalApiRequestSubscriber {

  protected function isTransactionalAction(string $entity, string $action): bool {
    return (str_starts_with($entity, 'Funding') || str_starts_with($entity, 'RemoteFunding'))
      && !str_starts_with($action, 'get');
  }

}
