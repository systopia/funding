<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Token;

use Civi\Core\Format;
use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\ClearingProcess\ClearingResourcesItemManager;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemTypeInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @extends AbstractClearingTableGenerator<
 *   \Civi\Funding\Entity\ApplicationResourcesItemEntity,
 *   \Civi\Funding\Entity\ClearingResourcesItemEntity
 * >
 */
final class ClearingResourcesTableGenerator extends AbstractClearingTableGenerator {

  public function __construct(
    private readonly ApplicationResourcesItemManager $appResourcesItemManager,
    private readonly ClearingResourcesItemManager $clearingResourcesItemManager,
    Format $format,
    FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
  ) {
    parent::__construct($format, $metaDataProvider);
  }

  /**
   * @inheritDoc
   */
  protected function getClearingItems(int $financePlanItemId): array {
    return $this->clearingResourcesItemManager->getByResourcesItemId($financePlanItemId);
  }

  /**
   * @inheritDoc
   */
  protected function getFinancePlanItems(int $applicationProcessId): array {
    return $this->appResourcesItemManager->getByApplicationProcessId($applicationProcessId);
  }

  protected function getFinancePlanItemType(
    FundingCaseTypeMetaDataInterface $metaData,
    string $name
  ): ?ResourcesItemTypeInterface {
    return $metaData->getResourcesItemType($name);
  }

  protected function getFooterLabel(): string {
    return E::ts('Total of resources receipts');
  }

}
