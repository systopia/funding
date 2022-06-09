<?php
declare(strict_types = 1);

namespace Civi\Funding\Contact;

use Civi\RemoteTools\Contact\RemoteContactIdResolverInterface;
use Webmozart\Assert\Assert;

class FundingRemoteContactIdResolver implements RemoteContactIdResolverInterface {

  /**
   * @inheritDoc
   */
  public function getContactId($remoteAuthenticateToken): int {
    // TODO

    Assert::integerish($remoteAuthenticateToken);

    return (int) $remoteAuthenticateToken;
  }

}
