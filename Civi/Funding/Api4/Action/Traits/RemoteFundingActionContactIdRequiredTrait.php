<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\Traits;

trait RemoteFundingActionContactIdRequiredTrait {

  use RemoteFundingActionContactIdTrait;

  /**
   * @var string
   * @required
   * @noinspection PhpDocFieldTypeMismatchInspection
   */
  protected ?string $remoteContactId = NULL;

  /**
   * @return string
   */
  public function getRemoteContactId(): ?string {
    return $this->remoteContactId;
  }

}
