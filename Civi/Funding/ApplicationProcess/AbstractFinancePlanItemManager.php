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

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\Entity\AbstractFinancePlanItemEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Webmozart\Assert\Assert;

/**
 * @template T of AbstractFinancePlanItemEntity
 */
abstract class AbstractFinancePlanItemManager {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-return T|null
   *
   * @throws \CRM_Core_Exception
   */
  public function get(int $id): ?AbstractFinancePlanItemEntity {
    $values = $this->api4->getEntity($this->getApiEntityName(), $id);

    // @phpstan-ignore-next-line
    return NULL === $values ? NULL : $this->getEntityClass()::fromArray($values);
  }

  /**
   * @phpstan-param T $item
   *
   * @throws \CRM_Core_Exception
   */
  public function delete(AbstractFinancePlanItemEntity $item): void {
    Assert::false($item->isNew(), 'Finance plan item is not persisted');

    $this->api4->deleteEntity($this->getApiEntityName(), $item->getId());
  }

  /**
   * @phpstan-return array<string, T>
   *   Finance plan items indexed by "identifier".
   *
   * @throws \CRM_Core_Exception
   */
  public function getByApplicationProcessId(int $applicationProcessId): array {
    $result = $this->api4->getEntities(
      $this->getApiEntityName(),
      Comparison::new('application_process_id', '=', $applicationProcessId)
    )->indexBy('identifier');

    // @phpstan-ignore-next-line
    return $this->getEntityClass()::allFromApiResult($result);
  }

  /**
   * @phpstan-param T $item
   *
   * @throws \CRM_Core_Exception
   */
  public function save(AbstractFinancePlanItemEntity $item): void {
    if ($item->isNew()) {
      $result = $this->api4->createEntity($this->getApiEntityName(), $item->toArray());
    }
    else {
      $result = $this->api4->updateEntity($this->getApiEntityName(), $item->getId(), $item->toArray());
    }

    // @phpstan-ignore-next-line
    $item->setValues($result->single());
  }

  /**
   * @phpstan-param array<T> $items
   *
   * @throws \CRM_Core_Exception
   */
  public function updateAll(int $applicationProcessId, array $items): void {
    $currentItems = $this->getByApplicationProcessId($applicationProcessId);
    $newIdentifiers = [];

    foreach ($items as $item) {
      Assert::same($applicationProcessId, $item->getApplicationProcessId());
      if (isset($currentItems[$item->getIdentifier()])) {
        $currentItem = $currentItems[$item->getIdentifier()];
        $currentItem->setType($item->getType());
        $currentItem->setAmount($item->getAmount());
        $currentItem->setProperties($item->getProperties());
        $currentItem->setDataPointer($item->getDataPointer());
        $this->save($currentItem);
      }
      else {
        $this->save($item);
      }
      $newIdentifiers[] = $item->getIdentifier();
    }

    foreach (array_diff(array_keys($currentItems), $newIdentifiers) as $deletedIdentifier) {
      $this->delete($currentItems[$deletedIdentifier]);
    }
  }

  abstract protected function getApiEntityName(): string;

  /**
   * @phpstan-return class-string<T>
   */
  abstract protected function getEntityClass(): string;

}
