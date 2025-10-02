<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Upgrade;

use Civi\Api4\FundingClearingCostItem;
use Civi\Api4\FundingClearingResourcesItem;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ApplicationCostItemFixture;
use Civi\Funding\Fixtures\ApplicationResourcesItemFixture;
use Civi\Funding\Fixtures\ClearingCostItemFixture;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\Fixtures\ClearingResourcesItemFixture;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0017
 *
 * @group headless
 */
final class Upgrader0017Test extends AbstractFundingHeadlessTestCase {

  protected function setUp(): void {
    parent::setUp();
  }

  public function testExecute(): void {
    $clearingBundle = ClearingProcessBundleFixture::create();
    $clearingProcess = $clearingBundle->getClearingProcess();
    $applicationProcess = $clearingBundle->getApplicationProcess();
    $applicationCostItem1 = ApplicationCostItemFixture::addFixture($applicationProcess->getId());
    $applicationCostItem2 = ApplicationCostItemFixture::addFixture($applicationProcess->getId());
    $applicationResourcesItem1 = ApplicationResourcesItemFixture::addFixture($applicationProcess->getId());
    $applicationResourcesItem2 = ApplicationResourcesItemFixture::addFixture($applicationProcess->getId());

    $clearingCostItem1 = ClearingCostItemFixture::addFixture($clearingProcess->getId(), $applicationCostItem1->getId());
    $clearingCostItem2 = ClearingCostItemFixture::addFixture($clearingProcess->getId(), $applicationCostItem2->getId());
    $clearingCostItem3 = ClearingCostItemFixture::addFixture($clearingProcess->getId(), $applicationCostItem1->getId());
    $clearingCostItem4 = ClearingCostItemFixture::addFixture($clearingProcess->getId(), $applicationCostItem2->getId());

    $clearingResourcesItem1 = ClearingResourcesItemFixture::addFixture(
      $clearingProcess->getId(),
      $applicationResourcesItem1->getId()
    );
    $clearingResourcesItem2 = ClearingResourcesItemFixture::addFixture(
      $clearingProcess->getId(),
      $applicationResourcesItem2->getId()
    );
    $clearingResourcesItem3 = ClearingResourcesItemFixture::addFixture(
      $clearingProcess->getId(),
      $applicationResourcesItem1->getId()
    );
    $clearingResourcesItem4 = ClearingResourcesItemFixture::addFixture(
      $clearingProcess->getId(),
      $applicationResourcesItem2->getId()
    );

    /** @var \Civi\Funding\Upgrade\Upgrader0017 $upgrader */
    $upgrader = \Civi::service(Upgrader0017::class);
    $upgrader->execute(new \Log_null('test'));

    $clearingCostItemGetAction = FundingClearingCostItem::get(FALSE)->addSelect('form_key');

    static::assertSame(
      $applicationCostItem1->getId() . '/0',
      $clearingCostItemGetAction->setWhere([['id', '=', $clearingCostItem1->getId()]])
        ->execute()->single()['form_key']
    );
    static::assertSame(
      $applicationCostItem2->getId() . '/0',
      $clearingCostItemGetAction->setWhere([['id', '=', $clearingCostItem2->getId()]])
        ->execute()->single()['form_key']
    );
    static::assertSame(
      $applicationCostItem1->getId() . '/1',
      $clearingCostItemGetAction->setWhere([['id', '=', $clearingCostItem3->getId()]])
        ->execute()->single()['form_key']
    );
    static::assertSame(
      $applicationCostItem2->getId() . '/1',
      $clearingCostItemGetAction->setWhere([['id', '=', $clearingCostItem4->getId()]])
        ->execute()->single()['form_key']
    );

    $clearingResourcesItemGetAction = FundingClearingResourcesItem::get(FALSE)->addSelect('form_key');

    static::assertSame(
      $applicationResourcesItem1->getId() . '/0',
      $clearingResourcesItemGetAction->setWhere([['id', '=', $clearingResourcesItem1->getId()]])
        ->execute()->single()['form_key']
    );
    static::assertSame(
      $applicationResourcesItem2->getId() . '/0',
      $clearingResourcesItemGetAction->setWhere([['id', '=', $clearingResourcesItem2->getId()]])
        ->execute()->single()['form_key']
    );
    static::assertSame(
      $applicationResourcesItem1->getId() . '/1',
      $clearingResourcesItemGetAction->setWhere([['id', '=', $clearingResourcesItem3->getId()]])
        ->execute()->single()['form_key']
    );
    static::assertSame(
      $applicationResourcesItem2->getId() . '/1',
      $clearingResourcesItemGetAction->setWhere([['id', '=', $clearingResourcesItem4->getId()]])
        ->execute()->single()['form_key']
    );
  }

}
