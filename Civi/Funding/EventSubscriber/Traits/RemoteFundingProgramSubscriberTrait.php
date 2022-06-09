<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\Traits;

use Civi\RemoteTools\Event\AbstractRequestEvent;

trait RemoteFundingProgramSubscriberTrait {

  protected function getExcludedFields(AbstractRequestEvent $event): array {
    return ['budget'];
  }

}
