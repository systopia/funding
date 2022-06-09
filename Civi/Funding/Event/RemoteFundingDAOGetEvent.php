<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteFundingEventContactIdRequiredTrait;
use Civi\RemoteTools\Event\DAOGetEvent;

class RemoteFundingDAOGetEvent extends DAOGetEvent {

  use RemoteFundingEventContactIdRequiredTrait;

}
