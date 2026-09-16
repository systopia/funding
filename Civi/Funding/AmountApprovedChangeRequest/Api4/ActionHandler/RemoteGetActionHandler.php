<?php

declare(strict_types = 1);

namespace Civi\Funding\AmountApprovedChangeRequest\Api4\ActionHandler;

use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetActionHandler;

final class RemoteGetActionHandler extends AbstractRemoteFundingGetActionHandler {

  public const ENTITY_NAME = 'RemoteFundingAmountApprovedChangeRequest';

}
