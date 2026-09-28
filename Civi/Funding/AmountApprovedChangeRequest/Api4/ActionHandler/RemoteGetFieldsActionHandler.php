<?php

declare(strict_types = 1);

namespace Civi\Funding\AmountApprovedChangeRequest\Api4\ActionHandler;

use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetFieldsActionHandler;

final class RemoteGetFieldsActionHandler extends AbstractRemoteFundingGetFieldsActionHandler {

  public const ENTITY_NAME = 'RemoteFundingAmountApprovedChangeRequest';

}
