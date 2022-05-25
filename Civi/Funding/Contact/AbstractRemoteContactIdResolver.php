<?php
declare(strict_types = 1);

namespace Civi\Funding\Contact;

abstract class AbstractRemoteContactIdResolver implements RemoteContactIdResolverInterface {

  /**
   * @inheritDoc
   */
  public function getUFId($remoteContactId): ?int {
    $contactId = $this->getContactId($remoteContactId);
    if (NULL === $contactId) {
      return NULL;
    }

    return \CRM_Core_BAO_UFMatch::getUFId($contactId);
  }

}
