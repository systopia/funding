<?php
declare(strict_types = 1);

namespace Civi\Funding\Event;

final class FundingEvents {

  public const REMOTE_REQUEST_INIT_EVENT_NAME = 'civi.funding.remote.request.init';

  public const REMOTE_REQUEST_AUTHORIZE_EVENT_NAME = 'civi.funding.remote.request.authorize';

}
