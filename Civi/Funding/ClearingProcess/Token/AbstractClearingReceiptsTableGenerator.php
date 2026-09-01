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
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\CostItemTypeInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemTypeInterface;
use Civi\Funding\FundingPseudoConstants;
use CRM_Funding_ExtensionUtil as E;

/**
 * @template FinancePlanItemT of \Civi\Funding\Entity\AbstractFinancePlanItemEntity
 * @template ClearingItemT of \Civi\Funding\Entity\AbstractClearingItemEntity
 */
abstract class AbstractClearingReceiptsTableGenerator {

  public function __construct(
    private readonly Format $format,
    private readonly FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
  ) {}

  public function generateReceiptsTable(ClearingProcessEntityBundle $clearingProcessBundle): string {
    $metaData = $this->metaDataProvider->get($clearingProcessBundle->getFundingCaseType()->getName());
    $currency = $clearingProcessBundle->getFundingProgram()->getCurrency();
    $itemStatuses = FundingPseudoConstants::getClearingItemStatus();

    $lineItemLabel = E::ts('Line item');
    $numberLabel = E::ts('No.');
    $statusLabel = E::ts('Status');
    $recordedLabel = E::ts('Recorded');
    $admittedLabel = E::ts('Admitted');
    $rows = 0;

    $table = <<<EOD
      <table style="width: 100%;">
        <thead>
          <tr>
            <th style="width: 50%;"><b>{$lineItemLabel}</b></th>
            <th style="width: 15%;"><b>{$numberLabel}</b></th>
            <th style="width: 15%;"><b>{$statusLabel}</b></th>
            <th style="width: 10%;"><b>{$recordedLabel}</b></th>
            <th style="width: 10%;"><b>{$admittedLabel}</b></th>
          </tr>
        </thead>
        <tbody>

      EOD;

    $financePlanItems = $this->getFinancePlanItems($clearingProcessBundle->getApplicationProcess()->getId());
    $financePlanItemsByType = [];
    foreach ($financePlanItems as $financePlanItem) {
      $financePlanItemsByType[$financePlanItem->getType()][] = $financePlanItem;
    }

    foreach ($financePlanItemsByType as $type => $typeFinancePlanItem) {
      $financePlanItemType = $this->getFinancePlanItemType($metaData, $type);
      $clearingLabel = $financePlanItemType?->getClearingLabel() ?? $type;
      if (count($typeFinancePlanItem) > 1 && 0 === preg_match('/\{pos[,}]/', $clearingLabel)) {
        $clearingLabel .= ' {pos}';
      }

      foreach ($typeFinancePlanItem as $index => $financePlanItem) {
        $clearingItems = $this->getClearingItems($financePlanItem->getId());

        foreach ($clearingItems as $clearingItem) {
          ++$rows;
          $lineItemLabel = \MessageFormatter::formatMessage(
            \CRM_Core_I18n::getLocale(),
            $clearingLabel,
            ['pos' => $index + 1]
          );
          $admitted = NULL === $clearingItem->getAmountAdmitted()
            ? '-'
            : $this->format->money(
              $clearingItem->getAmountAdmitted(),
              $currency
            );
          $table .= <<<EOD
            <tr>
              <td>$lineItemLabel</td>
              <td>{$clearingItem->getReceiptNumber()}</td>
              <td>{$itemStatuses[$clearingItem->getStatus()]}</td>
              <td>{$this->format->money($clearingItem->getAmount(), $currency)}</td>
              <td>{$admitted}</td>
            </tr>

            EOD;
        }
      }
    }

    if (0 === $rows) {
      $noReceiptsText = E::ts('- No receipts available -');
      $table .= <<<EOD
        <tr>
          <td colspan="5" style="text-align: center;">{$noReceiptsText}</td>
        </tr>

        EOD;

    }

    $table .= <<<EOD
        </tbody>
      </table>
      EOD;

    return $table;
  }

  /**
   * @return array<int, ClearingItemT>
   *
   * @throws \CRM_Core_Exception
   */
  abstract protected function getClearingItems(int $financePlanItemId): array;

  /**
   * @return array<string, FinancePlanItemT>
   *
   * @throws \CRM_Core_Exception
   */
  abstract protected function getFinancePlanItems(int $applicationProcessId): array;

  abstract protected function getFinancePlanItemType(
    FundingCaseTypeMetaDataInterface $metaData,
    string $name
  ): CostItemTypeInterface|ResourcesItemTypeInterface|null;

}
