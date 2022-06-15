<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber\Traits;

use Civi\RemoteTools\Event\AbstractRequestEvent;

trait RemoteFundingCaseTypeSubscriberTrait {

  protected function getIncludedFields(AbstractRequestEvent $event): array {
    return ['id', 'title'];
  }

}
