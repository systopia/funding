<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\GetAllowedActionsMultipleAction
 * @covers \Civi\Funding\ApplicationProcess\Api4\ActionHandler\GetAllowedActionsMultipleActionHandler
 * @covers \Civi\Api4\FundingApplicationProcess
 *
 * @group headless
 */
final class GetAllowedActionsMultipleActionTest extends AbstractFundingHeadlessTestCase {

  public function test(): void {
    $applicationProcess = $this->createApplicationProcess(['status' => 'review']);
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $applicationProcess->getFundingCaseId(),
      ['review_calculative'],
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $result = FundingApplicationProcess::getAllowedActionsMultiple()
      ->setIds([$applicationProcess->getId()])
      ->execute();

    static::assertEquals(
      [
        $applicationProcess->getId() => [
          'approve-calculative' => ['label' => 'Approve Calculative', 'confirm' => NULL],
          'reject-calculative' => ['label' => 'Reject Calculative', 'confirm' => NULL],
          'request-change' => ['label' => 'Request Change', 'confirm' => NULL],
          'reject' => ['label' => 'Reject', 'confirm' => NULL],
        ],
      ],
      $result->getArrayCopy()
    );
  }

  /**
   * @phpstan-param array<string, mixed> $values
   */
  private function createApplicationProcess(array $values): ApplicationProcessEntity {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual(['first_name' => 'creation', 'last_name' => 'contact']);

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );

    return ApplicationProcessFixture::addFixture($fundingCase->getId(), $values);
  }

}
