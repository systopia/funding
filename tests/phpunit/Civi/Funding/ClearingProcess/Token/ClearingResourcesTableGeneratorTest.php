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
 * @covers \Civi\Funding\ClearingProcess\Token\AbstractClearingTableGenerator
 * @covers \Civi\Funding\ClearingProcess\Token\ClearingResourcesTableGenerator
 */
final class ClearingResourcesTableGeneratorTest extends TestCase {

  private ApplicationResourcesItemManager&MockObject $appResourcesItemManagerMock;

  private ClearingResourcesItemManager&MockObject $clearingResourcesItemManagerMock;

  private Format&MockObject $formatMock;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private ClearingResourcesTableGenerator $tableGenerator;

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

    $this->tableGenerator = new ClearingResourcesTableGenerator(
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
    $this->metaDataMock->addResourcesItemType(new ResourcesItemType([
      'name' => 'type2',
      'label' => 'Type Two',
      'clearable' => TRUE,
    ]));

    $clearingProcessBundle = ClearingProcessBundleFactory::create();

    $appResourcesItem1 = ApplicationResourcesItemFactory::createApplicationResourcesItem([
      'identifier' => 'test1',
      'type' => 'type1',
      'amount' => 1.11,
    ]);
    $clearingResourcesItem1_1 = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem1->getId(),
      'amount' => 0.4,
      'amount_admitted' => 0.3,
    ]);
    $clearingResourcesItem1_2 = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem1->getId(),
      'amount' => 0.2,
    ]);

    $appResourcesItem1b = ApplicationResourcesItemFactory::createApplicationResourcesItem([
      'identifier' => 'test1b',
      'type' => 'type1',
      'amount' => 1.23,
    ]);
    $clearingResourcesItem1b = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem1b->getId(),
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
      'amount' => 0.7,
      'amount_admitted' => 0.1,
    ]);
    $clearingResourcesItem2_2 = ClearingResourcesItemFactory::create([
      'application_resources_item_id' => $appResourcesItem2->getId(),
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
    $result = $this->tableGenerator->generateTable($clearingProcessBundle);
    $expected = <<<EOD
      <table style="width: 100%;">
        <tbody>
          <tr>
            <th style="width: 70%;"><b></b></th>
            <th style="width: 10%;"><b>Approved</b></th>
            <th style="width: 10%;"><b>Recorded</b></th>
            <th style="width: 10%;"><b>Admitted</b></th>
          </tr>
          <tr>
            <th><b>Type One</b></th>
            <th><b>€2.34</b></th>
            <th><b>€0.80</b></th>
            <th><b>€0.40</b></th>
          </tr>
          <tr>
            <td>Clearing One 1</td>
            <td>€1.11</td>
            <td>€0.60</td>
            <td>€0.30</td>
          </tr>
          <tr>
            <td>Clearing One 2</td>
            <td>€1.23</td>
            <td>€0.20</td>
            <td>€0.10</td>
          </tr>
          <tr>
            <th style="width: 70%;"><b></b></th>
            <th style="width: 10%;"><b>Approved</b></th>
            <th style="width: 10%;"><b>Recorded</b></th>
            <th style="width: 10%;"><b>Admitted</b></th>
          </tr>
          <tr>
            <th><b>Type Two</b></th>
            <th><b>€2.22</b></th>
            <th><b>€0.90</b></th>
            <th><b>€0.10</b></th>
          </tr>
          <tr>
            <td>Type Two</td>
            <td>€2.22</td>
            <td>€0.90</td>
            <td>€0.10</td>
          </tr>
          <tr>
            <th style="width: 70%;"><b></b></th>
            <th style="width: 10%;"><b>Approved</b></th>
            <th style="width: 10%;"><b>Recorded</b></th>
            <th style="width: 10%;"><b>Admitted</b></th>
          </tr>
          <tr>
            <th><b>type3</b></th>
            <th><b>€3.33</b></th>
            <th><b>€0.00</b></th>
            <th><b>€0.00</b></th>
          </tr>
          <tr>
            <td>type3</td>
            <td>€3.33</td>
            <td>€0.00</td>
            <td>€0.00</td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th style="width: 70%;"><b>---</b></th>
            <th style="width: 10%;"><b>Approved</b></th>
            <th style="width: 10%;"><b>Recorded</b></th>
            <th style="width: 10%;"><b>Admitted</b></th>
          </tr>
          <tr>
            <th><b>Total of resources receipts</b></th>
            <th><b>€7.89</b></th>
            <th><b>€1.70</b></th>
            <th><b>€0.50</b></th>
          </tr>
        </tfoot>
      </table>
      EOD;

    static::assertXmlStringEqualsXmlString($expected, $result);
  }

}
