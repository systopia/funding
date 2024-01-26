<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcessActivity;

use Civi\Api4\RemoteFundingApplicationProcessActivity;
use Civi\Funding\Api4\Action\Remote\RemoteFundingGetActionLegacy;
use Civi\Funding\Api4\Action\Traits\ApplicationProcessIdParameterTrait;
use Civi\Funding\Event\Remote\ApplicationProcessActivity\GetEvent;

final class GetAction extends RemoteFundingGetActionLegacy {

  use ApplicationProcessIdParameterTrait;

  public function __construct() {
    parent::__construct(RemoteFundingApplicationProcessActivity::getEntityName(), 'get');
  }

  protected function getEventClass(): string {
    return GetEvent::class;
  }

}
