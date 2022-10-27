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

namespace Civi\RemoteTools\Event;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\RemoteTools\Event\FilterPossiblePermissionsEvent
 */
final class FilterPossiblePermissionsEventTest extends TestCase {

  public function testGetName(): void {
    static::assertSame(
      FilterPossiblePermissionsEvent::class . '@entity',
      FilterPossiblePermissionsEvent::getName('entity')
    );
  }

  public function testKeepPermissions(): void {
    $event = new FilterPossiblePermissionsEvent('entity', ['foo1', 'foo2', 'bar1', 'bar2']);
    $event->keepPermissions(['foo2', 'bar1', 'baz']);
    static::assertSame(['foo2', 'bar1'], $event->getPermissions());
  }

  public function testKeepPermissionsByPrefix(): void {
    $event = new FilterPossiblePermissionsEvent('entity', ['foo1', 'foo2', 'bar1', 'bar2']);
    $event->keepPermissionsByPrefix('b');
    static::assertSame(['bar1', 'bar2'], $event->getPermissions());
  }

  public function testRemovePermission(): void {
    $event = new FilterPossiblePermissionsEvent('entity', ['foo1', 'foo2', 'bar1', 'bar2']);
    $event->removePermission('foo2');
    static::assertSame(['foo1', 'bar1', 'bar2'], $event->getPermissions());
  }

  public function testRemovePermissions(): void {
    $event = new FilterPossiblePermissionsEvent('entity', ['foo1', 'foo2', 'bar1', 'bar2']);
    $event->removePermissions(['bar2', 'foo2', 'baz']);
    static::assertSame(['foo1', 'bar1'], $event->getPermissions());
  }

  public function testRemovePermissionsByPrefix(): void {
    $event = new FilterPossiblePermissionsEvent('entity', ['foo1', 'foo2', 'bar1', 'bar2']);
    $event->removePermissionsByPrefix('b');
    static::assertSame(['foo1', 'foo2'], $event->getPermissions());
  }

}
