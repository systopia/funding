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

use Civi\RemoteTools\Api3\Api3Interface;
use Civi\RemoteTools\Exception\ResolveContactIdFailedException;

final class IdentityTrackerRemoteContactIdResolver implements RemoteContactIdResolverInterface {

  private Api3Interface $api3;

  private string $identifierType;

  public function __construct(Api3Interface $api3, string $identifierType = 'remote_contact') {
    $this->api3 = $api3;
    $this->identifierType = $identifierType;
  }

  /**
   * @inheritDoc
   */
  public function getContactId($remoteAuthenticationToken): int {
    try {
      /** @var array<string, mixed>&array{id: int, values: array<int, array{id: int}>} $result */
      $result = $this->api3->execute('Contact', 'identify', [
        'identifier' => $remoteAuthenticationToken,
        'identifier_type' => $this->identifierType,
      ]);

      return $result['id'];
    }
    catch (\Exception $e) {
      throw new ResolveContactIdFailedException($e->getMessage(), $e->getCode(), $e);
    }
  }

}
