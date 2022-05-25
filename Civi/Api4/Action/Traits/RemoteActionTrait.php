<?php
declare(strict_types = 1);

namespace Civi\Api4\Action\Traits;

trait RemoteActionTrait {

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

  public function setRemoteContactId($remoteContactId): self {
    $this->remoteContactId = $remoteContactId;

    return $this;
  }

}
