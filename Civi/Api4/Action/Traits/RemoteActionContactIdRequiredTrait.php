<?php
declare(strict_types = 1);

namespace Civi\Api4\Action\Traits;

trait RemoteActionContactIdRequiredTrait {

  use RemoteActionTrait;

  /**
   * @var string|int
   * @required
   */
  protected $remoteContactId;

  /**
   * @return int|string
   */
  public function getRemoteContactId() {
    return $this->remoteContactId;
  }

}
