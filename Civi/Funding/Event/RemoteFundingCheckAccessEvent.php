<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteFundingEventContactIdRequiredTrait;
use Civi\RemoteTools\Event\CheckAccessEvent;

class RemoteFundingCheckAccessEvent extends CheckAccessEvent {

  use RemoteFundingEventContactIdRequiredTrait;

}
