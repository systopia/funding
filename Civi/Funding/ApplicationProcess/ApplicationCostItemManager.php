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

namespace Civi\Funding\ApplicationProcess;

use Civi\Api4\FundingApplicationCostItem;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type applicationCostItemT array{
 *   id?: int,
 *   application_process_id: int,
 *   identifier: string,
 *   type: string,
 *   amount: float,
 *   properties: array<int|string, mixed>,
 * }
 */
class ApplicationCostItemManager {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  public function delete(ApplicationCostItemEntity $applicationResourcesItem): void {
    Assert::false($applicationResourcesItem->isNew(), 'Application resources item is not persisted');

    $action = FundingApplicationCostItem::delete()
      ->addWhere('id', '=', $applicationResourcesItem->getId());
    $this->api4->executeAction($action);
  }

  /**
   * @phpstan-return array<string, ApplicationCostItemEntity>
   *   Application resources items indexed by "identifier".
   *
   * @throws \API_Exception
   */
  public function getByApplicationProcessId(int $applicationProcessId): array {
    $action = FundingApplicationCostItem::get()
      ->addWhere('application_process_id', '=', $applicationProcessId);
    /** @phpstan-var iterable<applicationCostItemT> $result */
    $result = $this->api4->executeAction($action)->indexBy('identifier');

    return self::toEntities($result);
  }

  public function save(ApplicationCostItemEntity $applicationResourcesItem): void {
    if ($applicationResourcesItem->isNew()) {
      $action = FundingApplicationCostItem::create()->setValues($applicationResourcesItem->toArray());
    }
    else {
      $action = FundingApplicationCostItem::update()->setValues($applicationResourcesItem->toArray());
    }

    /** @phpstan-var applicationCostItemT $values */
    $values = $this->api4->executeAction($action)->first();
    $applicationResourcesItem->setValues($values);
  }

  /**
   * @phpstan-param array<ApplicationCostItemEntity> $items
   *
   * @throws \API_Exception
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

  /**
   * @phpstan-param iterable<applicationCostItemT> $records
   *
   * @phpstan-return array<ApplicationCostItemEntity>
   *   Application resources items (keys are unchanged).
   */
  private static function toEntities(iterable $records): array {
    $entities = [];
    foreach ($records as $key => $record) {
      $entities[$key] = ApplicationCostItemEntity::fromArray($record);
    }

    return $entities;
  }

}