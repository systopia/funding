<?php

declare(strict_types = 1);

namespace Civi\Funding\Api4\ActionHandler\RemoteAmountApprovedChangeRequest;

use Civi\Api4\FundingAmountApprovedChangeRequest;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetFieldsActionHandler;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

// phpcs:ignore Generic.Files.LineLength.TooLong
final class GetFieldsActionHandler extends AbstractRemoteFundingGetFieldsActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingAmountApprovedChangeRequest';

  protected function getEntityName(): string {
    return FundingAmountApprovedChangeRequest::getEntityName();
  }

}
