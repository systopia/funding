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
use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ClearingProcess\ClearingCostItemManager;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\CostItemTypeInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @extends AbstractClearingTableGenerator<
 *   \Civi\Funding\Entity\ApplicationCostItemEntity,
 *   \Civi\Funding\Entity\ClearingCostItemEntity
 * >
 */
final class ClearingCostsTableGenerator extends AbstractClearingTableGenerator {

  public function __construct(
    private readonly ApplicationCostItemManager $appCostItemManager,
    private readonly ClearingCostItemManager $clearingCostItemManager,
    Format $format,
    FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
  ) {
    parent::__construct($format, $metaDataProvider);
  }

  /**
   * @inheritDoc
   */
  protected function getClearingItems(int $financePlanItemId): array {
    return $this->clearingCostItemManager->getByCostItemId($financePlanItemId);
  }

  /**
   * @inheritDoc
   */
  protected function getFinancePlanItems(int $applicationProcessId): array {
    return $this->appCostItemManager->getByApplicationProcessId($applicationProcessId);
  }

  protected function getFinancePlanItemType(
    FundingCaseTypeMetaDataInterface $metaData,
    string $name
  ): ?CostItemTypeInterface {
    return $metaData->getCostItemType($name);
  }

  protected function getFooterLabel(): string {
    return E::ts('Total of cost receipts');
  }

}
