<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\AbstractRemoteFundingEntityLegacy;
use Civi\Funding\Api4\Action\Remote\ApplicationProcessActivity\GetAction;

final class RemoteFundingApplicationProcessActivity extends AbstractRemoteFundingEntityLegacy {

  public static function get(): GetAction {
    return new GetAction();
  }

}
