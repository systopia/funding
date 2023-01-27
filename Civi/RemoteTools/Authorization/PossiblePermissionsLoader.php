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

use Civi\Core\CiviEventDispatcherInterface;
use Civi\RemoteTools\Event\FilterPossiblePermissionsEvent;
use Civi\RemoteTools\Event\GetPossiblePermissionsEvent;
use Psr\SimpleCache\CacheInterface;

/**
 * Gets possible permissions for an entity via a Symfony event.
 *
 * @see GetPossiblePermissionsEvent
 */
final class PossiblePermissionsLoader implements PossiblePermissionsLoaderInterface {

  private CacheInterface $cache;

  private CiviEventDispatcherInterface $eventDispatcher;

  /**
   * @phpstan-var array<string, array<string, string>>
   */
  private array $permissions = [];

  public function __construct(CiviEventDispatcherInterface $eventDispatcher, CacheInterface $cache) {
    $this->eventDispatcher = $eventDispatcher;
    $this->cache = $cache;
  }

  public function getFilteredPermissions(string $entityName): array {
    $permissions = $this->getPermissions($entityName);
    $event = new FilterPossiblePermissionsEvent($entityName, $permissions);
    $this->eventDispatcher->dispatch(FilterPossiblePermissionsEvent::getName($entityName), $event);

    return $event->getPermissions();
  }

  /**
   * @inheritDoc
   */
  public function getPermissions(string $entityName): array {
    return $this->permissions[$entityName] ??= $this->doGetPermissions($entityName);
  }

  /**
   * @phpstan-return array<string, string>
   */
  private function doGetPermissions(string $entityName): array {
    $cacheKey = 'possible-permissions.' . $entityName;
    if ($this->cache->has($cacheKey)) {
      /** @phpstan-var array<string> */
      return $this->cache->get($cacheKey);
    }

    $event = new GetPossiblePermissionsEvent($entityName);
    $this->eventDispatcher->dispatch(GetPossiblePermissionsEvent::getName($entityName), $event);
    $this->cache->set($cacheKey, $event->getPermissions());

    return $event->getPermissions();
  }

}
