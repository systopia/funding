<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Fixtures\ApplicationCostItemFixture;
use Civi\Funding\Fixtures\ApplicationResourcesItemFixture;
use Civi\Funding\Fixtures\ClearingCostItemFixture;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\Fixtures\ClearingResourcesItemFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\FundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\FundingClearingProcess\GetAction
 *
 * @group headless
 */
final class FundingClearingProcessTest extends AbstractFundingHeadlessTestCase {

  protected function setUp(): void {
    parent::setUp();
  }

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $contactNotPermitted = ContactFixture::addIndividual();
    $clearingProcessBundle = $this->createClearingProcessBundle([
      'is_review_calculative' => TRUE,
      'is_review_content' => TRUE,
      'status' => 'accepted',
    ]);
    $clearingProcessId = $clearingProcessBundle->getClearingProcess()->getId();
    $applicationProcessId = $clearingProcessBundle->getApplicationProcess()->getId();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $clearingProcessBundle->getFundingCase()->getId(),
      ['review_test'],
    );

    RequestTestUtil::mockInternalRequest($contact['id']);
    static::assertSame(
      [$clearingProcessBundle->getClearingProcess()->toArray()],
      FundingClearingProcess::get()->execute()->getArrayCopy()
    );

    static::assertSame([
      [
        'id' => $clearingProcessId,
        'amount_recorded_costs' => 0.0,
        'amount_recorded_resources' => 0.0,
        'amount_admitted_costs' => 0.0,
        'amount_admitted_resources' => 0.0,
      ],
    ], FundingClearingProcess::get()->addSelect(
        'amount_recorded_costs',
        'amount_recorded_resources',
        'amount_admitted_costs',
        'amount_admitted_resources',
      )->execute()->getArrayCopy()
    );

    $applicationCostItem = ApplicationCostItemFixture::addFixture($applicationProcessId);
    ClearingCostItemFixture::addFixture($clearingProcessId, $applicationCostItem->getId(), [
      'amount' => 10.1,
      'amount_admitted' => 9.1,
      'status' => 'accepted',
    ]);
    ClearingCostItemFixture::addFixture($clearingProcessId, $applicationCostItem->getId(), [
      'amount' => 1.1,
      'amount_admitted' => 0,
      'status' => 'rejected',
    ]);

    $applicationResourcesItem = ApplicationResourcesItemFixture::addFixture($applicationProcessId);
    ClearingResourcesItemFixture::addFixture($clearingProcessId, $applicationResourcesItem->getId(), [
      'amount' => 3.2,
      'amount_admitted' => 2.2,
      'status' => 'accepted',
    ]);
    ClearingResourcesItemFixture::addFixture($clearingProcessId, $applicationResourcesItem->getId(), [
      'amount' => 1.1,
      'amount_admitted' => 0,
      'status' => 'rejected',
    ]);

    static::assertSame([
      [
        'id' => $clearingProcessId,
        'amount_recorded_costs' => 10.1,
        'amount_recorded_resources' => 3.2,
        'amount_admitted_costs' => 9.1,
        'amount_admitted_resources' => 2.2,
      ],
    ], FundingClearingProcess::get()->addSelect(
      'amount_recorded_costs',
      'amount_recorded_resources',
      'amount_admitted_costs',
      'amount_admitted_resources',
    )->execute()->getArrayCopy());

    RequestTestUtil::mockInternalRequest($contactNotPermitted['id']);
    static::assertCount(0, FundingClearingProcess::get()->execute());
  }

  public function testUpdateNotPermitted(): void {
    static::expectException(UnauthorizedException::class);
    FundingClearingProcess::update()
      ->addValue('x', 'y')
      ->addWhere('y', '=', 'z')
      ->execute();
  }

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  private function createClearingProcessBundle(array $values = []): ClearingProcessEntityBundle {
    return ClearingProcessBundleFixture::create($values + ['status' => 'review']);
  }

}
