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
use Civi\Funding\EntityFactory\ApplicationResourcesItemFactory;
use Civi\Funding\EntityFactory\ClearingResourcesItemFactory;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\FundingCaseType\MetaData\ResourcesItemType;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Token\AbstractClearingReceiptsTableGenerator
 * @covers \Civi\Funding\ClearingProcess\Token\ClearingResourcesReceiptsTableGenerator
 */
final class ClearingResourcesReceiptsTableGeneratorTest extends TestCase {

  private ApplicationResourcesItemManager&MockObject $appResourcesItemManagerMock;

  private ClearingResourcesItemManager&MockObject $clearingResourcesItemManagerMock;

  private Format&MockObject $formatMock;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private ClearingResourcesReceiptsTableGenerator $receiptsTableGenerator;

  protected function setUp(): void {
    parent::setUp();
    $this->appResourcesItemManagerMock = $this->createMock(ApplicationResourcesItemManager::class);
    $this->clearingResourcesItemManagerMock = $this->createMock(ClearingResourcesItemManager::class);
    $this->formatMock = $this->createMock(Format::class);
    $this->formatMock->method('money')->willReturnCallback(
      function (float $amount, string $currency) {
        return \NumberFormatter::create('en_US', \NumberFormatter::CURRENCY)->formatCurrency($amount, $currency);
      }
    );
    $this->metaDataMock = new FundingCaseTypeMetaDataMock();

    $this->receiptsTableGenerator = new ClearingResourcesReceiptsTableGenerator(
      $this->appResourcesItemManagerMock,
      $this->clearingResourcesItemManagerMock,
      $this->formatMock,
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock)
    );
  }

  public function testGenerate(): void {
    $this->metaDataMock->addResourcesItemType(new ResourcesItemType([
      'name' => 'type1',
      'label' => 'Type One',
      'clearable' => TRUE,
      'clearingLabel' => 'Clearing One',
    ]));

    $clearingProcessBundle = ClearingProcessBundleFactory::create();

    $appResourcesItem1 = ApplicationResourcesItemFactory::createApplicationResourcesItem([
      'identifier' => 'test1',
      'type' => 'type1',
      'amount' => 1.11,
    ]);
    $clearingResourcesItem1_1 = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem1->getId(),
      'receipt_number' => '1_1',
      'amount' => 0.4,
      'amount_admitted' => 0.3,
    ]);
    $clearingResourcesItem1_2 = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem1->getId(),
      'receipt_number' => '1_2',
      'amount' => 0.2,
    ]);

    $appResourcesItem1b = ApplicationResourcesItemFactory::createApplicationResourcesItem([
      'identifier' => 'test1b',
      'type' => 'type1',
      'amount' => 1.23,
    ]);
    $clearingResourcesItem1b = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem1b->getId(),
      'receipt_number' => '1b',
      'amount' => 0.2,
      'amount_admitted' => 0.1,
    ]);

    $appResourcesItem2 = ApplicationResourcesItemFactory::createApplicationResourcesItem([
      'identifier' => 'test2',
      'type' => 'type2',
      'amount' => 2.22,
    ]);
    $clearingResourcesItem2_1 = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem2->getId(),
      'receipt_number' => '2_1',
      'amount' => 0.7,
      'amount_admitted' => 0.1,
    ]);
    $clearingResourcesItem2_2 = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem2->getId(),
      'receipt_number' => '2_2',
      'amount' => 0.2,
    ]);

    $appResourcesItem3 = ApplicationResourcesItemFactory::createApplicationResourcesItem([
      'identifier' => 'test3',
      'type' => 'type3',
      'amount' => 3.33,
    ]);

    $this->appResourcesItemManagerMock->method('getByApplicationProcessId')
      ->with($clearingProcessBundle->getApplicationProcess()->getId())
      ->willReturn([
        $appResourcesItem1->getIdentifier() => $appResourcesItem1,
        $appResourcesItem1b->getIdentifier() => $appResourcesItem1b,
        $appResourcesItem2->getIdentifier() => $appResourcesItem2,
        $appResourcesItem3->getIdentifier() => $appResourcesItem3,
      ]);

    $this->clearingResourcesItemManagerMock->method('getByResourcesItemId')
      ->willReturnCallback(
        fn(int $resourcesItemId) => match ($resourcesItemId) {
          $appResourcesItem1->getId() => [
            $clearingResourcesItem1_1->getId() => $clearingResourcesItem1_1,
            $clearingResourcesItem1_2->getId() => $clearingResourcesItem1_2,
          ],
          $appResourcesItem1b->getId() => [
            $clearingResourcesItem1b->getId() => $clearingResourcesItem1b,
          ],
          $appResourcesItem2->getId() => [
            $clearingResourcesItem2_1->getId() => $clearingResourcesItem2_1,
            $clearingResourcesItem2_2->getId() => $clearingResourcesItem2_2,
          ],
          $appResourcesItem3->getId() => [],
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

    $appResourcesItem1 = ApplicationResourcesItemFactory::createApplicationResourcesItem([
      'identifier' => 'test1',
      'type' => 'type1',
      'amount' => 1.11,
    ]);

    $this->appResourcesItemManagerMock->method('getByApplicationProcessId')
      ->with($clearingProcessBundle->getApplicationProcess()->getId())
      ->willReturn([
        $appResourcesItem1->getIdentifier() => $appResourcesItem1,
      ]);

    $this->clearingResourcesItemManagerMock->method('getByResourcesItemId')
      ->with($appResourcesItem1->getId())
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
