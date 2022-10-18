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

namespace Civi\RemoteTools\Authorization;

use Civi\Core\CiviEventDispatcher;
use Civi\RemoteTools\Event\GetPossiblePermissionsEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers \Civi\RemoteTools\Authorization\PossiblePermissionsLoader
 * @covers \Civi\RemoteTools\Event\GetPossiblePermissionsEvent
 */
final class PossiblePermissionsLoaderTest extends TestCase {

  /**
   * @var \Psr\SimpleCache\CacheInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $cacheMock;

  /**
   * @var \Civi\Core\CiviEventDispatcher&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  private PossiblePermissionsLoader $possiblePermissionsLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->cacheMock = $this->createMock(CacheInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->possiblePermissionsLoader = new PossiblePermissionsLoader(
      $this->eventDispatcherMock,
      $this->cacheMock,
    );
  }

  public function testGetPermissions(): void {
    $this->cacheMock->expects(static::once())->method('has')
      ->with('possible-permissions.test')
      ->willReturn(FALSE);

    $event = new GetPossiblePermissionsEvent('test');
    static::assertSame('test', $event->getEntityName());
    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(GetPossiblePermissionsEvent::getName('test'), $event)
      ->willReturnCallback(function (string $eventName, GetPossiblePermissionsEvent $event) {
        $event->addPermission('foo')->addPermissions(['bar', 'baz']);
      });

    static::assertSame(['foo', 'bar', 'baz'], $this->possiblePermissionsLoader->getPermissions('test'));
    // second call uses internal cache
    static::assertSame(['foo', 'bar', 'baz'], $this->possiblePermissionsLoader->getPermissions('test'));
  }

  public function testGetPermissionsCached(): void {
    $this->eventDispatcherMock->expects(static::never())->method('dispatch');

    $this->cacheMock->expects(static::once())->method('has')
      ->with('possible-permissions.test')
      ->willReturn(TRUE);

    $this->cacheMock->expects(static::once())->method('get')
      ->with('possible-permissions.test')
      ->willReturn(['cached']);

    static::assertSame(['cached'], $this->possiblePermissionsLoader->getPermissions('test'));
    // second call uses internal cache
    static::assertSame(['cached'], $this->possiblePermissionsLoader->getPermissions('test'));
  }

}
