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
use Civi\Funding\Entity\AbstractClearingItemEntity;
use Civi\Funding\Entity\AbstractFinancePlanItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\CostItemTypeInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemTypeInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @template FinancePlanItemT of \Civi\Funding\Entity\AbstractFinancePlanItemEntity
 * @template ClearingItemT of \Civi\Funding\Entity\AbstractClearingItemEntity
 */
abstract class AbstractClearingTableGenerator {

  public function __construct(
    private readonly Format $format,
    private readonly FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
  ) {}

  // phpcs:ignore Generic.Metrics.CyclomaticComplexity.TooHigh
  public function generateTable(ClearingProcessEntityBundle $clearingProcessBundle): string {
    $metaData = $this->metaDataProvider->get($clearingProcessBundle->getFundingCaseType()->getName());
    $currency = $clearingProcessBundle->getFundingProgram()->getCurrency();

    $approvedLabel = E::ts('Approved');
    $recordedLabel = E::ts('Recorded');
    $admittedLabel = E::ts('Admitted');

    $approvedOverall = 0.0;
    $recordedOverall = 0.0;
    $admittedOverall = 0.0;

    $table = <<<EOD
      <table style="width: 100%;">
        <tbody>

      EOD;

    $financePlanItems = $this->getFinancePlanItems($clearingProcessBundle->getApplicationProcess()->getId());
    $financePlanItemsByType = [];
    $clearingItemsByFinancePlanItem = [];
    foreach ($financePlanItems as $financePlanItem) {
      $financePlanItemsByType[$financePlanItem->getType()][] = $financePlanItem;
      $clearingItemsByFinancePlanItem[$financePlanItem->getId()] = $this->getClearingItems($financePlanItem->getId());
    }

    foreach ($financePlanItemsByType as $type => $typeFinancePlanItems) {
      $typeLabel = $this->getFinancePlanItemType($metaData, $type)?->getLabel() ?? $type;
      $approvedOverall += $approvedSum = array_reduce(
        $typeFinancePlanItems,
        fn($carry, AbstractFinancePlanItemEntity $item) => $carry + $item->getAmount(),
        0.0
      );

      $recordedSum = 0.0;
      $admittedSum = 0.0;
      $recordedSumsByFinancePlanItem = [];
      $admittedSumsByFinancePlanItem = [];

      foreach ($typeFinancePlanItems as $financePlanItem) {
        $recordedSum += $recordedSumsByFinancePlanItem[$financePlanItem->getId()] = array_reduce(
          $clearingItemsByFinancePlanItem[$financePlanItem->getId()],
          fn($carry, AbstractClearingItemEntity $item) => $carry + $item->getAmount(),
          0.0
        );

        $admittedSum += $admittedSumsByFinancePlanItem[$financePlanItem->getId()] = array_reduce(
          $clearingItemsByFinancePlanItem[$financePlanItem->getId()],
          fn($carry, AbstractClearingItemEntity $item) => $carry + ($item->getAmountAdmitted() ?? 0.0),
          0.0
        );
      }

      $recordedOverall += $recordedSum;
      $admittedOverall += $admittedSum;

      $table .= <<<EOD
        <tr>
          <th style="width: 70%;"><b></b></th>
          <th style="width: 10%;"><b>$approvedLabel</b></th>
          <th style="width: 10%;"><b>$recordedLabel</b></th>
          <th style="width: 10%;"><b>$admittedLabel</b></th>
        </tr>
        <tr>
          <th><b>$typeLabel</b></th>
          <th><b>{$this->format->money($approvedSum, $currency)}</b></th>
          <th><b>{$this->format->money($recordedSum, $currency)}</b></th>
          <th><b>{$this->format->money($admittedSum, $currency)}</b></th>
        </tr>

        EOD;

      $clearingLabel = $this->getFinancePlanItemType($metaData, $type)?->getClearingLabel() ?? $type;
      if (count($typeFinancePlanItems) > 1 && 0 === preg_match('/\{pos[,}]/', $clearingLabel)) {
        $clearingLabel .= ' {pos}';
      }
      foreach ($typeFinancePlanItems as $index => $financePlanItem) {
        $lineItemLabel = \MessageFormatter::formatMessage(
          \CRM_Core_I18n::getLocale(),
          $clearingLabel,
          ['pos' => $index + 1]
        );
        $table .= <<<EOD
          <tr>
            <td>$lineItemLabel</td>
            <td>{$this->format->money($financePlanItem->getAmount(), $currency)}</td>
            <td>{$this->format->money($recordedSumsByFinancePlanItem[$financePlanItem->getId()], $currency)}</td>
            <td>{$this->format->money($admittedSumsByFinancePlanItem[$financePlanItem->getId()], $currency)}</td>
          </tr>

          EOD;
      }
    }

    $footerLabel = $this->getFooterLabel();

    $table .= <<<EOD
        </tbody>
        <tfoot>
          <tr>
            <th style="width: 70%;"><b>---</b></th>
            <th style="width: 10%;"><b>$approvedLabel</b></th>
            <th style="width: 10%;"><b>$recordedLabel</b></th>
            <th style="width: 10%;"><b>$admittedLabel</b></th>
          </tr>
          <tr>
            <th><b>$footerLabel</b></th>
            <th><b>{$this->format->money($approvedOverall, $currency)}</b></th>
            <th><b>{$this->format->money($recordedOverall, $currency)}</b></th>
            <th><b>{$this->format->money($admittedOverall, $currency)}</b></th>
          </tr>
        </tfoot>
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

  abstract protected function getFooterLabel(): string;

}
