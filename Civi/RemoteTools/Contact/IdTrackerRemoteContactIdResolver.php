<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Contact;

use CRM_Xcm_Matcher_IdTrackerMatcher;

final class IdTrackerRemoteContactIdResolver implements RemoteContactIdResolverInterface {

  private CRM_Xcm_Matcher_IdTrackerMatcher $idTrackerMatcher;

  public function __construct(string $identityType) {
    $this->idTrackerMatcher = new CRM_Xcm_Matcher_IdTrackerMatcher($identityType, 'remoteContactId');
  }

  /**
   * @inheritDoc
   */
  public function getContactId($remoteAuthenticateToken): ?int {
    $data = ['remoteContactId' => $remoteAuthenticateToken];
    $matchResult = $this->idTrackerMatcher->matchContact($data);

    return $matchResult['contact_id'] ?? NULL;
  }

}
