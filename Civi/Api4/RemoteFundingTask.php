<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\AbstractRemoteFundingEntity;
use Civi\Funding\Api4\Action\Remote\RemoteFundingGetAction;

final class RemoteFundingTask extends AbstractRemoteFundingEntity {

  public static function get(): RemoteFundingGetAction {
    return new RemoteFundingGetAction(self::getEntityName(), __FUNCTION__);
  }

}
