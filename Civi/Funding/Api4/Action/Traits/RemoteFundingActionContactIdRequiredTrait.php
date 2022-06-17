<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\Traits;

trait RemoteFundingActionContactIdRequiredTrait {

  /**
   * @var string
   * @required
   */
  protected string $remoteContactId;

  public function getRemoteContactId(): string {
    return $this->remoteContactId;
  }

  public function setRemoteContactId(string $remoteContactId): self {
    $this->remoteContactId = $remoteContactId;

    return $this;
  }

}
