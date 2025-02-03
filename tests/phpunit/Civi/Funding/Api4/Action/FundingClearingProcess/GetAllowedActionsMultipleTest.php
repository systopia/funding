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

namespace Civi\Funding\Api4\Action\FundingClearingProcess;

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\FundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\FundingClearingProcess\GetAllowedActionsMultipleAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\GetAllowedActionsMultipleActionHandler
 *
 * @group headless
 */
final class GetAllowedActionsMultipleTest extends AbstractFundingHeadlessTestCase {

  public function test(): void {
    $clearingProcessBundle = ClearingProcessBundleFixture::create(['status' => 'accepted']);
    $contact = ContactFixture::addIndividual();
    RequestTestUtil::mockInternalRequest($contact['id']);

    $action = FundingClearingProcess::getAllowedActionsMultiple()
      ->setIds([$clearingProcessBundle->getClearingProcess()->getId()]);

    static::assertSame(
      [$clearingProcessBundle->getClearingProcess()->getId() => []],
      $action->execute()->getArrayCopy()
    );

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CONTENT],
    );

    static::assertEquals([
      $clearingProcessBundle->getClearingProcess()->getId() => [
        'request-change' => ['label' => 'Request Change', 'confirm' => NULL],
        'review' => ['label' => 'Review', 'confirm' => NULL],
      ],
    ], $action->execute()->getArrayCopy());
  }

}
