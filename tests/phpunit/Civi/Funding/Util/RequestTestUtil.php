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

namespace Civi\Funding\Util;

use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

final class RequestTestUtil {

  public static function mockInternalRequest(int $contactId): void {
    static::reset();
    \CRM_Core_Session::singleton()->set('userID', $contactId);
  }

  public static function mockRemoteRequest(string $remoteContactId): void {
    Assert::integerish($remoteContactId);
    self::reset();
    /** @var \Civi\RemoteTools\RequestContext\RequestContextInterface $requestContext */
    $requestContext = \Civi::service(RequestContextInterface::class);
    $requestContext->setRemote(TRUE);
    $requestContext->setRemoteContactId($remoteContactId);
    $requestContext->setResolvedContactId((int) $remoteContactId);
  }

  private static function reset(): void {
    \CRM_Core_Session::singleton()->reset();
    /** @var \Civi\RemoteTools\RequestContext\RequestContextInterface $requestContext */
    $requestContext = \Civi::service(RequestContextInterface::class);
    $requestContext->setRemote(FALSE);
    $requestContext->setRemoteContactId(NULL);
    $requestContext->setResolvedContactId(NULL);
  }

}
