<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\AbstractRemoteFundingEntity;
use Civi\Funding\Api4\Action\RemoteFundingDAOGetAction;
use Civi\Funding\Api4\Action\RemoteFundingProgramGetRelatedAction;

class RemoteFundingProgram extends AbstractRemoteFundingEntity {

  public static function get(): RemoteFundingDAOGetAction {
    return new RemoteFundingDAOGetAction(static::getEntityName());
  }

  public static function getRelated(): RemoteFundingProgramGetRelatedAction {
    return new RemoteFundingProgramGetRelatedAction();
  }

}
