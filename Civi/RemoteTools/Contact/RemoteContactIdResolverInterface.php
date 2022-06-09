<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Contact;

interface RemoteContactIdResolverInterface {

  /**
   * @param int|string $remoteAuthenticateToken
   *
   * @return int|null
   */
  public function getContactId($remoteAuthenticateToken): ?int;

}
