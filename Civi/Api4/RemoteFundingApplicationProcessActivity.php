<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\AbstractRemoteFundingEntity;
use Civi\Funding\Api4\Action\Remote\ApplicationProcessActivity\GetAction;

final class RemoteFundingApplicationProcessActivity extends AbstractRemoteFundingEntity {

  public static function get(): GetAction {
    return new GetAction();
  }

}
