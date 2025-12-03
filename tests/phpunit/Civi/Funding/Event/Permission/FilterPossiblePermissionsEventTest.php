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

namespace Civi\Funding\Event\Permission;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Event\Permission\FilterPossiblePermissionsEvent
 */
final class FilterPossiblePermissionsEventTest extends TestCase {

  public function testGetName(): void {
    static::assertSame(
      FilterPossiblePermissionsEvent::class . '@entity',
      FilterPossiblePermissionsEvent::getName('entity')
    );
  }

  public function testKeepPermissions(): void {
    $permissions = ['foo1' => 'Foo1', 'foo2' => 'Foo2', 'bar1' => 'Bar1', 'bar2' => 'Bar2'];
    $event = new FilterPossiblePermissionsEvent('entity', $permissions);
    $event->keepPermissions(['foo2', 'bar1', 'baz']);
    static::assertSame(['foo2' => 'Foo2', 'bar1' => 'Bar1'], $event->getPermissions());
  }

  public function testKeepPermissionsByPrefix(): void {
    $permissions = ['foo1' => 'Foo1', 'foo2' => 'Foo2', 'bar1' => 'Bar1', 'bar2' => 'Bar2'];
    $event = new FilterPossiblePermissionsEvent('entity', $permissions);
    $event->keepPermissionsByPrefix('b');
    static::assertSame(['bar1' => 'Bar1', 'bar2' => 'Bar2'], $event->getPermissions());
  }

  public function testKeepPermissionsByPrefixes(): void {
    $permissions = ['foo1' => 'Foo1', 'foo2' => 'Foo2', 'bar1' => 'Bar1', 'bar2' => 'Bar2', 'baz1' => 'Baz1'];
    $event = new FilterPossiblePermissionsEvent('entity', $permissions);
    $event->keepPermissionsByPrefixes(['bar', 'baz']);
    static::assertSame(['bar1' => 'Bar1', 'bar2' => 'Bar2', 'baz1' => 'Baz1'], $event->getPermissions());
  }

  public function testRemovePermission(): void {
    $permissions = ['foo1' => 'Foo1', 'foo2' => 'Foo2', 'bar1' => 'Bar1', 'bar2' => 'Bar2'];
    $event = new FilterPossiblePermissionsEvent('entity', $permissions);
    $event->removePermission('foo2');
    static::assertSame(['foo1' => 'Foo1', 'bar1' => 'Bar1', 'bar2' => 'Bar2'], $event->getPermissions());
  }

  public function testRemovePermissions(): void {
    $permissions = ['foo1' => 'Foo1', 'foo2' => 'Foo2', 'bar1' => 'Bar1', 'bar2' => 'Bar2'];
    $event = new FilterPossiblePermissionsEvent('entity', $permissions);
    $event->removePermissions(['bar2', 'foo2', 'baz']);
    static::assertSame(['foo1' => 'Foo1', 'bar1' => 'Bar1'], $event->getPermissions());
  }

  public function testRemovePermissionsByPrefix(): void {
    $permissions = ['foo1' => 'Foo1', 'foo2' => 'Foo2', 'bar1' => 'Bar1', 'bar2' => 'Bar2'];
    $event = new FilterPossiblePermissionsEvent('entity', $permissions);
    $event->removePermissionsByPrefix('b');
    static::assertSame(['foo1' => 'Foo1', 'foo2' => 'Foo2'], $event->getPermissions());
  }

}
