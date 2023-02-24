<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Session;

use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

/**
 * @covers \Civi\Funding\Session\FundingSession
 */
final class FundingSessionTest extends TestCase {

  private FundingSession $session;

  protected function setUp(): void {
    parent::setUp();
    $this->session = new FundingSession(\CRM_Core_Session::singleton());
  }

  protected function tearDown(): void {
    parent::tearDown();
    \CRM_Core_Session::singleton()->reset();
  }

  public function testCLI(): void {
    static::assertFalse($this->session->isRemote());
    static::assertSame(0, $this->session->getContactId());
  }

  public function testInternal(): void {
    \CRM_Core_Session::singleton()->set('userID', 1);
    static::assertFalse($this->session->isRemote());
    static::assertSame(1, $this->session->getContactId());
  }

  public function testRemote(): void {
    \CRM_Core_Session::singleton()->set('userID', 1);
    $this->session->setRemote(TRUE);
    $this->session->setResolvedContactId(2);
    static::assertTrue($this->session->isRemote());
    static::assertSame(2, $this->session->getContactId());
  }

  public function testRemoteNoResolvedContactId(): void {
    \CRM_Core_Session::singleton()->set('userID', 1);
    $this->session->setRemote(TRUE);
    static::assertTrue($this->session->isRemote());

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Resolved contact ID missing');
    $this->session->getContactId();
  }

}
