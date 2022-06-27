<?php
declare(strict_types = 1);

namespace Civi\Funding\Event\Traits;

trait RemoteFundingEventContactIdRequiredTrait {

  protected int $contactId;

  protected string $remoteContactId;

  public function getContactId(): int {
    return $this->contactId;
  }

  public function getRemoteContactId(): string {
    return $this->remoteContactId;
  }

  /**
   * @return string[]
   */
  protected function getRequiredParams(): array {
    return array_merge(parent::getRequiredParams(), ['contactId', 'remoteContactId']);
  }

}
