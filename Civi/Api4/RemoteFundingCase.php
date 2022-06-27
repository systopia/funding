<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\AbstractRemoteFundingEntity;
use Civi\Funding\Api4\Action\RemoteFundingCaseGetNewApplicationFormAction;
use Civi\Funding\Api4\Action\RemoteFundingCaseSubmitNewApplicationFormAction;
use Civi\Funding\Api4\Action\RemoteFundingCaseValidateNewApplicationFormAction;
use Civi\Funding\Api4\Action\RemoteFundingDAOGetAction;

final class RemoteFundingCase extends AbstractRemoteFundingEntity {

  public static function get(): RemoteFundingDAOGetAction {
    return new RemoteFundingDAOGetAction(static::getEntityName());
  }

  public static function getNewApplicationForm(): RemoteFundingCaseGetNewApplicationFormAction {
    return new RemoteFundingCaseGetNewApplicationFormAction();
  }

  public static function submitNewApplicationForm(): RemoteFundingCaseSubmitNewApplicationFormAction {
    return new RemoteFundingCaseSubmitNewApplicationFormAction();
  }

  public static function validateNewApplicationForm(): RemoteFundingCaseValidateNewApplicationFormAction {
    return new RemoteFundingCaseValidateNewApplicationFormAction();
  }

}
