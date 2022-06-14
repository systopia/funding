<?php
declare(strict_types = 1);

namespace Civi\RemoteTools\Contact;

use CRM_Xcm_Matcher_IdTrackerMatcher;

final class IdTrackerRemoteContactIdResolver implements RemoteContactIdResolverInterface {

  private const REMOTE_CONTACT_ID_FIELD = 'remoteContactId';

  private CRM_Xcm_Matcher_IdTrackerMatcher $idTrackerMatcher;

  public function __construct(string $identityType) {
    $this->idTrackerMatcher = new CRM_Xcm_Matcher_IdTrackerMatcher($identityType, [self::REMOTE_CONTACT_ID_FIELD]);
  }

  /**
   * @inheritDoc
   */
  public function getContactId($remoteAuthenticateToken): ?int {
    $data = [self::REMOTE_CONTACT_ID_FIELD => $remoteAuthenticateToken];
    $matchResult = $this->idTrackerMatcher->matchContact($data);

    return $matchResult['contact_id'] ?? NULL;
  }

}
