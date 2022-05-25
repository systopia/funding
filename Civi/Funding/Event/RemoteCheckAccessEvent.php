<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteApiEventTrait;

class RemoteCheckAccessEvent extends CheckAccessEvent {

  use RemoteApiEventTrait;

}
