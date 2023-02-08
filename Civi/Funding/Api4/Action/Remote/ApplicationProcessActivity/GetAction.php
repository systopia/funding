<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcessActivity;

use Civi\Api4\RemoteFundingApplicationProcessActivity;
use Civi\Funding\Api4\Action\Remote\RemoteFundingGetAction;
use Civi\Funding\Event\Remote\ApplicationProcessActivity\GetEvent;

/**
 * @method $this setApplicationProcessId(int $applicationProcessId)
 */
final class GetAction extends RemoteFundingGetAction {

  /**
   * @var int
   * @required
   */
  protected ?int $applicationProcessId = NULL;

  public function __construct() {
    parent::__construct(RemoteFundingApplicationProcessActivity::_getEntityName(), 'get');
  }

  protected function getEventClass(): string {
    return GetEvent::class;
  }

}
