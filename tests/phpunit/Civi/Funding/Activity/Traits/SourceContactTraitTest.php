<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Activity\Traits;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Core_BAO_Domain;

/**
 * @group headless
 *
 * @covers \Civi\Funding\Activity\Traits\SourceContactTrait
 */
class SourceContactTraitTest extends AbstractFundingHeadlessTestCase {

  use SourceContactTrait;

  public RequestContextInterface $requestContext;

  public function testGetResolvedSourceContactIdReturnsContextId(): void {
    $contactId = 123;
    $requestContextMock = $this->createMock(RequestContextInterface::class);
    $requestContextMock->method('getContactId')->willReturn($contactId);
    $this->requestContext = $requestContextMock;

    static::assertSame($contactId, $this->getResolvedSourceContactId());
  }

  public function testGetResolvedSourceContactIdReturnsDomainIdWhenContextIdIsZero(): void {
    $requestContextMock = $this->createMock(RequestContextInterface::class);
    $requestContextMock->method('getContactId')->willReturn(0);
    $this->requestContext = $requestContextMock;

    $domain = CRM_Core_BAO_Domain::getDomain();
    $expectedId = (int) $domain->contact_id;

    static::assertSame($expectedId, $this->getResolvedSourceContactId());
  }

}
