<?php
declare(strict_types = 1);

namespace Civi\Funding\Event\Traits;

trait RemoteApiEventContactIdRequiredTrait {

  use RemoteApiEventTrait;

  /**
   * @var int|string
   */
  protected $remoteContactId;

  /**
   * @return int|string
   */
  public function getRemoteContactId() {
    return $this->remoteContactId;
  }

  protected function getRequiredParams(): array {
    return parent::getRequiredParams() + ['remoteContactId'];
  }

}
