<?php
declare(strict_types = 1);

namespace Civi\Funding\Event\Traits;

trait RemoteFundingEventTrait {

  protected ?int $contactId;

  public function getContactId(): ?int {
    return $this->contactId;
  }

  protected ?string $remoteContactId;

  public function getRemoteContactId(): ?string {
    return $this->remoteContactId;
  }

}
