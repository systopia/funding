<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess;

use Civi\Funding\Entity\AbstractClearingItemEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

/**
 * @template T of AbstractClearingItemEntity
 */
abstract class AbstractClearingItemManager {

  protected Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-param T $item
   *
   * @throws \CRM_Core_Exception
   */
  public function delete(AbstractClearingItemEntity $item): void {
    Assert::false($item->isNew(), 'Clearing item is not persisted');

    $this->api4->deleteEntity($this->getApiEntityName(), $item->getId());
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * @phpstan-return T
   */
  public function get(int $id): ?AbstractClearingItemEntity {
    $values = $this->api4->getEntity($this->getApiEntityName(), $id);

    return NULL === $values ? NULL : $this->getEntityClass()::fromArray($values);
  }

  /**
   * @phpstan-return array<int, T>
   *   Clearing items indexed by id.
   *
   * @throws \CRM_Core_Exception
   */
  abstract public function getByApplicationProcessId(int $applicationProcessId): array;

  /**
   * @phpstan-param T $item
   *
   * @throws \CRM_Core_Exception
   */
  public function save(AbstractClearingItemEntity $item): void {
    if ($item->isNew()) {
      $result = $this->api4->createEntity($this->getApiEntityName(), $item->toArray());
    }
    else {
      $result = $this->api4->updateEntity($this->getApiEntityName(), $item->getId(), $item->toArray());
    }

    $item->setValues($result->single());
  }

  abstract protected function getApiEntityName(): string;

  /**
   * @phpstan-return class-string<T>
   */
  abstract protected function getEntityClass(): string;

}
