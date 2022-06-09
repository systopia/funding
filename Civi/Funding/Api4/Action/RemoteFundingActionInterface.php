<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\RemoteTools\Api4\Action\EventActionInterface;

interface RemoteFundingActionInterface extends EventActionInterface {

  public function getRemoteContactId(): ?string;

  public function setRemoteContactId(string $remoteContactId): self;

}
