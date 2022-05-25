<?php
declare(strict_types = 1);

namespace Civi\Funding\Event\Traits;

trait RemoteApiEventTrait {

  /**
   * @var int|string|null
   */
  protected $remoteContactId;

  /**
   * @return int|string|null
   */
  public function getRemoteContactId() {
    return $this->remoteContactId;
  }

}
