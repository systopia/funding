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

/**
 * @noinspection PropertyAnnotationInspection
 */

declare(strict_types = 1);

namespace Civi\RemoteTools\Contact;

use Civi\RemoteTools\Api3\Api3Interface;
use Civi\RemoteTools\Exception\ResolveContactIdFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Contact\IdentityTrackerRemoteContactIdResolver
 */
final class IdentityTrackerRemoteContactIdResolverTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api3\Api3Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api3Mock;

  private IdentityTrackerRemoteContactIdResolver $contactIdResolver;

  protected function setUp(): void {
    parent::setUp();
    $this->api3Mock = $this->createMock(Api3Interface::class);
    $this->contactIdResolver = new IdentityTrackerRemoteContactIdResolver(
      $this->api3Mock, 'test_identifier_type'
    );
  }

  /**
   * @throws \Civi\RemoteTools\Exception\ResolveContactIdFailedException
   */
  public function testGetContactId(): void {
    $this->api3Mock->expects(static::once())->method('execute')
      ->with('Contact', 'identify', [
        'identifier' => 'test',
        'identifier_type' => 'test_identifier_type',
      ])
      ->willReturn(['id' => 12, 'values' => [12 => ['id' => 12]]]);

    static::assertSame(12, $this->contactIdResolver->getContactId('test'));
  }

  public function testGetContactIdFail(): void {
    $this->api3Mock->expects(static::once())->method('execute')
      ->with('Contact', 'identify', [
        'identifier' => 'test',
        'identifier_type' => 'test_identifier_type',
      ])
      ->willThrowException(new \CRM_Core_Exception('Test'));

    $this->expectException(ResolveContactIdFailedException::class);
    $this->expectExceptionMessage('Test');
    $this->contactIdResolver->getContactId('test');
  }

}
