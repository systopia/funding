<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteApiEventContactIdRequiredTrait;

class RemoteGetEvent extends GetEvent {

  use RemoteApiEventContactIdRequiredTrait;

}
