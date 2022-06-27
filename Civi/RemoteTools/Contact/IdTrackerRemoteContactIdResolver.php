<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
