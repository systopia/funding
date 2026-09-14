<?php

declare(strict_types = 1);

namespace Civi\Funding\Api4\ActionHandler\RemoteAmountApprovedChangeRequest;

use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetActionHandler;

final class GetActionHandler extends AbstractRemoteFundingGetActionHandler {

  public const ENTITY_NAME = 'RemoteFundingAmountApprovedChangeRequest';

  protected function getEntityName(): string {
    return FundingAmountApprovedChangeRequest::getEntityName();
  }

}
