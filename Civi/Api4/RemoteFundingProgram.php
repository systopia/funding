<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Api4\Action\RemoteEventGetAction;
use Civi\Api4\Funding\AbstractRemoteFundingEntity;

class RemoteFundingProgram extends AbstractRemoteFundingEntity {

  public static function get(): RemoteEventGetAction {
    return new RemoteEventGetAction(static::getEntityName());
  }

}
