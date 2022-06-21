<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\Api4\Generic\AbstractAction;
use Civi\RemoteTools\Api4\Action\Traits\EventActionTrait;

abstract class AbstractRemoteFundingAction extends AbstractAction implements RemoteFundingActionInterface {

  use EventActionTrait;

}
