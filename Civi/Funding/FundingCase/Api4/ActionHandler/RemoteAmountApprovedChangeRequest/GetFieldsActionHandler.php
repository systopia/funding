<?php

declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Api4\ActionHandler\RemoteAmountApprovedChangeRequest;

use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetFieldsActionHandler;

// phpcs:ignore Generic.Files.LineLength.TooLong
final class GetFieldsActionHandler extends AbstractRemoteFundingGetFieldsActionHandler {

  public const ENTITY_NAME = 'RemoteFundingAmountApprovedChangeRequest';

  protected function getEntityName(): string {
    return FundingAmountApprovedChangeRequest::getEntityName();
  }

}
