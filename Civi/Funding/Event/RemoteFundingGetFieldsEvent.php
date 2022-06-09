<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

use Civi\Funding\Event\Traits\RemoteFundingEventTrait;
use Civi\RemoteTools\Event\GetFieldsEvent;

class RemoteFundingGetFieldsEvent extends GetFieldsEvent {

  use RemoteFundingEventTrait;

}
