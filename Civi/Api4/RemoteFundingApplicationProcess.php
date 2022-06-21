<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\AbstractRemoteFundingEntity;
use Civi\Funding\Api4\Action\RemoteFundingApplicationProcessGetFormAction;
use Civi\Funding\Api4\Action\RemoteFundingApplicationProcessSubmitFormAction;
use Civi\Funding\Api4\Action\RemoteFundingApplicationProcessValidateFormAction;
use Civi\Funding\Api4\Action\RemoteFundingDAOGetAction;

final class RemoteFundingApplicationProcess extends AbstractRemoteFundingEntity {

  public static function get(): RemoteFundingDAOGetAction {
    return new RemoteFundingDAOGetAction(static::getEntityName());
  }

  public static function getForm(): RemoteFundingApplicationProcessGetFormAction {
    return new RemoteFundingApplicationProcessGetFormAction();
  }

  public static function submitForm(): RemoteFundingApplicationProcessSubmitFormAction {
    return new RemoteFundingApplicationProcessSubmitFormAction();
  }

  public static function validateForm(): RemoteFundingApplicationProcessValidateFormAction {
    return new RemoteFundingApplicationProcessValidateFormAction();
  }

}
