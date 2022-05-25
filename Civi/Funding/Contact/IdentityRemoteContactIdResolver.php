<?php
declare(strict_types = 1);

namespace Civi\Funding\Contact;

use Webmozart\Assert\Assert;

class IdentityRemoteContactIdResolver extends AbstractRemoteContactIdResolver {

  /**
   * @inheritDoc
   */
  public function getContactId($remoteContactId): ?int {
    Assert::integerish($remoteContactId);

    return (int) $remoteContactId;
  }

}
