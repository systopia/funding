<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\Traits;

use Civi\Funding\Event\AbstractApiEvent;

trait RemoteFundingProgramSubscriberTrait {

  protected function getExcludedFields(AbstractApiEvent $event): array {
    return ['budget'];
  }

}
