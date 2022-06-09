<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\Traits;

trait RemoteFundingActionContactIdTrait {

  protected ?string $remoteContactId = NULL;

  public function getRemoteContactId(): ?string {
    return $this->remoteContactId;
  }

  public function setRemoteContactId(string $remoteContactId): self {
    $this->remoteContactId = $remoteContactId;

    return $this;
  }

}
