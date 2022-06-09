<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Event;

use Civi\API\Event\AuthorizedTrait;

class AuthorizeApiRequestEvent extends AbstractApiRequestEvent {

  use AuthorizedTrait;

}
