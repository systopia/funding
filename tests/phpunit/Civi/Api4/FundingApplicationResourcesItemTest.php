<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Api4;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ApplicationProcessBundleFixture;
use Civi\Funding\Fixtures\ApplicationResourcesItemFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\FundingApplicationResourcesItem
 * @covers \Civi\Funding\Api4\Action\FundingApplicationResourcesItem\GetFieldsAction
 * @covers \Civi\Funding\Api4\Action\Generic\FinancePlanItem\GetAction
 * @covers \Civi\Funding\Api4\Action\Generic\FinancePlanItem\AbstractGetFieldsAction
 *
 * @group headless
 */
final class FundingApplicationResourcesItemTest extends AbstractFundingHeadlessTestCase {

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    RequestTestUtil::mockInternalRequest($contact['id']);
    $applicationProcessBundle = ApplicationProcessBundleFixture::create();
    $resourcesItem = ApplicationResourcesItemFixture::addFixture(
      $applicationProcessBundle->getApplicationProcess()->getId()
    );
    $result = FundingApplicationResourcesItem::get()->execute();
    static::assertCount(0, $result);

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $applicationProcessBundle->getFundingCase()->getId(),
      ['test_permission']
    );
    $result = FundingApplicationResourcesItem::get()->execute();
    static::assertCount(1, $result);
    static::assertEquals($resourcesItem->toArray(), $result->first());

    $result = FundingApplicationResourcesItem::get()->addSelect('type_label')->execute();
    static::assertSame('Test resources', $result->first()['type_label'] ?? NULL);

    $result = FundingApplicationResourcesItem::get()->addSelect('funding_case_type')->execute();
    static::assertSame('TestCaseType', $result->first()['funding_case_type'] ?? NULL);
  }

  public function testGetFields(): void {
    $result = FundingApplicationResourcesItem::getFields()->execute();
    static::assertCount(10, $result);
  }

}
