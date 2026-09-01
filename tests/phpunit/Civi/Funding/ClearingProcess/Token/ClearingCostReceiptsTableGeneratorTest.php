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
use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ClearingCostItemFactory;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\FundingCaseType\MetaData\CostItemType;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Token\AbstractClearingReceiptsTableGenerator
 * @covers \Civi\Funding\ClearingProcess\Token\ClearingCostReceiptsTableGenerator
 */
final class ClearingCostReceiptsTableGeneratorTest extends TestCase {

  private ApplicationCostItemManager&MockObject $appCostItemManagerMock;

  private ClearingCostItemManager&MockObject $clearingCostItemManagerMock;

  private Format&MockObject $formatMock;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private ClearingCostReceiptsTableGenerator $receiptsTableGenerator;

  protected function setUp(): void {
    parent::setUp();
    $this->appCostItemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->clearingCostItemManagerMock = $this->createMock(ClearingCostItemManager::class);
    $this->formatMock = $this->createMock(Format::class);
    $this->formatMock->method('money')->willReturnCallback(
      function (float $amount, string $currency) {
        return \NumberFormatter::create('en_US', \NumberFormatter::CURRENCY)->formatCurrency($amount, $currency);
      }
    );
    $this->metaDataMock = new FundingCaseTypeMetaDataMock();

    $this->receiptsTableGenerator = new ClearingCostReceiptsTableGenerator(
      $this->appCostItemManagerMock,
      $this->clearingCostItemManagerMock,
      $this->formatMock,
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock)
    );
  }

  public function testGenerate(): void {
    $this->metaDataMock->addCostItemType(new CostItemType([
      'name' => 'type1',
      'label' => 'Type One',
      'clearable' => TRUE,
      'clearingLabel' => 'Clearing One',
    ]));

    $clearingProcessBundle = ClearingProcessBundleFactory::create();

    $appCostItem1 = ApplicationCostItemFactory::createApplicationCostItem([
      'identifier' => 'test1',
      'type' => 'type1',
      'amount' => 1.11,
    ]);
    $clearingCostItem1_1 = ClearingCostItemFactory::create([
      'application_cost_item_id' => $appCostItem1->getId(),
      'receipt_number' => '1_1',
      'amount' => 0.4,
      'amount_admitted' => 0.3,
    ]);
    $clearingCostItem1_2 = ClearingCostItemFactory::create([
      'application_cost_item_id' => $appCostItem1->getId(),
      'receipt_number' => '1_2',
      'amount' => 0.2,
    ]);

    $appCostItem1b = ApplicationCostItemFactory::createApplicationCostItem([
      'identifier' => 'test1b',
      'type' => 'type1',
      'amount' => 1.23,
    ]);
    $clearingCostItem1b = ClearingCostItemFactory::create([
      'application_cost_item_id' => $appCostItem1b->getId(),
      'receipt_number' => '1b',
      'amount' => 0.2,
      'amount_admitted' => 0.1,
    ]);

    $appCostItem2 = ApplicationCostItemFactory::createApplicationCostItem([
      'identifier' => 'test2',
      'type' => 'type2',
      'amount' => 2.22,
    ]);
    $clearingCostItem2_1 = ClearingCostItemFactory::create([
      'application_cost_item_id' => $appCostItem2->getId(),
      'receipt_number' => '2_1',
      'amount' => 0.7,
      'amount_admitted' => 0.1,
    ]);
    $clearingCostItem2_2 = ClearingCostItemFactory::create([
      'application_cost_item_id' => $appCostItem2->getId(),
      'receipt_number' => '2_2',
      'amount' => 0.2,
    ]);

    $appCostItem3 = ApplicationCostItemFactory::createApplicationCostItem([
      'identifier' => 'test3',
      'type' => 'type3',
      'amount' => 3.33,
    ]);

    $this->appCostItemManagerMock->method('getByApplicationProcessId')
      ->with($clearingProcessBundle->getApplicationProcess()->getId())
      ->willReturn([
        $appCostItem1->getIdentifier() => $appCostItem1,
        $appCostItem1b->getIdentifier() => $appCostItem1b,
        $appCostItem2->getIdentifier() => $appCostItem2,
        $appCostItem3->getIdentifier() => $appCostItem3,
      ]);

    $this->clearingCostItemManagerMock->method('getByCostItemId')
      ->willReturnCallback(
        fn(int $costItemId) => match ($costItemId) {
          $appCostItem1->getId() => [
            $clearingCostItem1_1->getId() => $clearingCostItem1_1,
            $clearingCostItem1_2->getId() => $clearingCostItem1_2,
          ],
          $appCostItem1b->getId() => [
            $clearingCostItem1b->getId() => $clearingCostItem1b,
          ],
          $appCostItem2->getId() => [
            $clearingCostItem2_1->getId() => $clearingCostItem2_1,
            $clearingCostItem2_2->getId() => $clearingCostItem2_2,
          ],
          $appCostItem3->getId() => [],
          default => throw new \InvalidArgumentException()
        }
      );

    // phpcs:disable Drupal.WhiteSpace.ScopeIndent.IncorrectExact
    // There seems to be an issue with the indent scope after the above code.
    $expected = <<<EOD
      <table style="width: 100%;">
        <thead>
          <tr>
            <th style="width: 50%;"><b>Line item</b></th>
            <th style="width: 15%;"><b>No.</b></th>
            <th style="width: 15%;"><b>Status</b></th>
            <th style="width: 10%;"><b>Recorded</b></th>
            <th style="width: 10%;"><b>Admitted</b></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Clearing One 1</td>
            <td>1_1</td>
            <td>New</td>
            <td>€0.40</td>
            <td>€0.30</td>
          </tr>
          <tr>
            <td>Clearing One 1</td>
            <td>1_2</td>
            <td>New</td>
            <td>€0.20</td>
            <td>-</td>
          </tr>
          <tr>
            <td>Clearing One 2</td>
            <td>1b</td>
            <td>New</td>
            <td>€0.20</td>
            <td>€0.10</td>
          </tr>
          <tr>
            <td>type2</td>
            <td>2_1</td>
            <td>New</td>
            <td>€0.70</td>
            <td>€0.10</td>
          </tr>
          <tr>
            <td>type2</td>
            <td>2_2</td>
            <td>New</td>
            <td>€0.20</td>
            <td>-</td>
          </tr>
        </tbody>
      </table>
      EOD;

    $result = $this->receiptsTableGenerator->generateReceiptsTable($clearingProcessBundle);
    static::assertXmlStringEqualsXmlString($expected, $result);
  }

  public function testGenerate_WithoutReceipt(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();

    $appCostItem1 = ApplicationCostItemFactory::createApplicationCostItem([
      'identifier' => 'test1',
      'type' => 'type1',
      'amount' => 1.11,
    ]);

    $this->appCostItemManagerMock->method('getByApplicationProcessId')
      ->with($clearingProcessBundle->getApplicationProcess()->getId())
      ->willReturn([
        $appCostItem1->getIdentifier() => $appCostItem1,
      ]);

    $this->clearingCostItemManagerMock->method('getByCostItemId')
      ->with($appCostItem1->getId())
      ->willReturn([]);

    $expected = <<<EOD
      <table style="width: 100%;">
        <thead>
          <tr>
            <th style="width: 50%;"><b>Line item</b></th>
            <th style="width: 15%;"><b>No.</b></th>
            <th style="width: 15%;"><b>Status</b></th>
            <th style="width: 10%;"><b>Recorded</b></th>
            <th style="width: 10%;"><b>Admitted</b></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td colspan="5" style="text-align: center;">- No receipts available -</td>
          </tr>
        </tbody>
      </table>
      EOD;

    $result = $this->receiptsTableGenerator->generateReceiptsTable($clearingProcessBundle);
    static::assertXmlStringEqualsXmlString($expected, $result);
  }

}
