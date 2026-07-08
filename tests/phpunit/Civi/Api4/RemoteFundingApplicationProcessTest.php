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

namespace Civi\Api4;

use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Fixtures\FundingCaseBundleFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;

/**
 * @covers \Civi\Api4\RemoteFundingApplicationProcess
 *
 * @group headless
 */
final class RemoteFundingApplicationProcessTest extends AbstractRemoteFundingHeadlessTestCase {

  /**
   * @covers \Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetAllowedActionsInitialByFundingCaseAction
   * @covers \Civi\Funding\ApplicationProcess\Api4\ActionHandler\RemoteGetAllowedActionsInitialByFundingCaseActionHandler
   */
  public function testGetAllowedActionsInitialByFundingCase(): void {
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCaseId = $fundingCaseBundle->getFundingCase()->getId();
    $contactId = $fundingCaseBundle->getFundingCase()->getCreationContactId();

    $action = RemoteFundingApplicationProcess::getAllowedActionsInitialByFundingCase()
      ->setRemoteContactId((string) $contactId)
      ->setFundingCaseIds([$fundingCaseId]);

    static::assertSame([$fundingCaseId => []], $action->execute()->getArrayCopy());

    FundingCaseContactRelationFixture::addContact($contactId, $fundingCaseId, ['application_create']);
    static::assertEquals(
      [
        $fundingCaseId => [
          'save' => [
            'label' => 'Save',
            'confirm' => NULL,
          ],
        ],
      ],
      $action->execute()->getArrayCopy()
    );
  }

}
