<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcessActivity;

use Civi\Api4\RemoteFundingApplicationProcessActivity;
use Civi\Funding\Api4\Action\Remote\RemoteFundingGetAction;
use Civi\Funding\Api4\Action\Traits\ApplicationProcessIdParameterTrait;
use Civi\Funding\Event\Remote\ApplicationProcessActivity\GetEvent;

final class GetAction extends RemoteFundingGetAction {

  use ApplicationProcessIdParameterTrait;

  public function __construct() {
    parent::__construct(RemoteFundingApplicationProcessActivity::_getEntityName(), 'get');
  }

  protected function getEventClass(): string {
    return GetEvent::class;
  }

}
