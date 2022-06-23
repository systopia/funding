<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\Traits;

use Civi\Funding\Api4\Action\RemoteFundingActionInterface;

trait RemoteFundingActionContactIdRequiredTrait {

  /**
   * Must be initialized because it is directly accessed in AbstractAction.
   *
   * @var string|null
   * @required
   */
  protected ?string $remoteContactId = NULL;

  public function getRemoteContactId(): ?string {
    return $this->remoteContactId;
  }

  /**
   * @param string $remoteContactId
   *
   * @return $this
   */
  public function setRemoteContactId(string $remoteContactId): RemoteFundingActionInterface {
    $this->remoteContactId = $remoteContactId;

    return $this;
  }

}
