<?php
declare(strict_types = 1);

namespace Civi\Funding\Contact;

interface RemoteContactIdResolverInterface {

  /**
   * @param int|string $remoteContactId
   *
   * @return int|null
   */
  public function getContactId($remoteContactId): ?int;

  /**
   * @param int|string $remoteContactId
   *
   * @return int|null
   */
  public function getUFId($remoteContactId): ?int;

}
