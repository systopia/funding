<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Permission;

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Event\Permission\FilterPossiblePermissionsEvent;
use Civi\Funding\Event\Permission\GetPossiblePermissionsEvent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers \Civi\Funding\Permission\PossiblePermissionsLoader
 * @covers \Civi\Funding\Event\Permission\GetPossiblePermissionsEvent
 */
final class PossiblePermissionsLoaderTest extends TestCase {

  /**
   * @var \Psr\SimpleCache\CacheInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $cacheMock;

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  private PossiblePermissionsLoader $possiblePermissionsLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->cacheMock = $this->createMock(CacheInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
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
        $event->addPermission('foo', 'Foo')->addPermissions(['bar' => 'Bar', 'baz' => 'Baz']);
      });

    static::assertSame(
      ['foo' => 'Foo', 'bar' => 'Bar', 'baz' => 'Baz'],
      $this->possiblePermissionsLoader->getPermissions('test')
    );
    // second call uses internal cache
    static::assertSame(
      ['foo' => 'Foo', 'bar' => 'Bar', 'baz' => 'Baz'],
      $this->possiblePermissionsLoader->getPermissions('test')
    );
  }

  public function testGetPermissionsCached(): void {
    $this->eventDispatcherMock->expects(static::never())->method('dispatch');

    $this->cacheMock->expects(static::once())->method('has')
      ->with('possible-permissions.test')
      ->willReturn(TRUE);

    $this->cacheMock->expects(static::once())->method('get')
      ->with('possible-permissions.test')
      ->willReturn(['cached' => 'Label']);

    static::assertSame(['cached' => 'Label'], $this->possiblePermissionsLoader->getPermissions('test'));
    // second call uses internal cache
    static::assertSame(['cached' => 'Label'], $this->possiblePermissionsLoader->getPermissions('test'));
  }

  public function testGetPermissionsCacheClear(): void {
    $this->eventDispatcherMock->expects(static::never())->method('dispatch');

    $this->cacheMock->expects(static::exactly(2))->method('has')
      ->with('possible-permissions.test')
      ->willReturn(TRUE);

    $this->cacheMock->expects(static::exactly(2))->method('get')
      ->with('possible-permissions.test')
      ->willReturn(['cached' => 'Label']);

    static::assertSame(['cached' => 'Label'], $this->possiblePermissionsLoader->getPermissions('test'));
    $this->possiblePermissionsLoader->clearCache('test');
    static::assertSame(['cached' => 'Label'], $this->possiblePermissionsLoader->getPermissions('test'));
  }

  public function testGetPermissionsFiltered(): void {
    $this->cacheMock->expects(static::once())->method('has')
      ->with('possible-permissions.test')
      ->willReturn(TRUE);

    $permissions = ['cached1' => 'Label1', 'cached2' => 'Label2'];
    $this->cacheMock->expects(static::once())->method('get')
      ->with('possible-permissions.test')
      ->willReturn($permissions);

    $event = new FilterPossiblePermissionsEvent('test', $permissions);
    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(FilterPossiblePermissionsEvent::getName('test'), $event)
      ->willReturnCallback(function (string $eventName, FilterPossiblePermissionsEvent $event) {
        $event->removePermission('cached1');
      });

    static::assertSame(['cached2' => 'Label2'], $this->possiblePermissionsLoader->getFilteredPermissions('test'));
  }

}
