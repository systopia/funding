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

namespace Civi\Funding\Api4\Action\FundingCase;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingCase;
use Civi\Api4\FundingPayoutProcess;
use Civi\Api4\Traits\FundingCaseTestFixturesTrait;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\AttachmentFixture;
use Civi\Funding\Fixtures\ClearingProcessFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;
use Civi\Funding\Util\RequestTestUtil;
use CRM_Funding_ExtensionUtil as E;

/**
 * @covers \Civi\Api4\FundingCase
 * @covers \Civi\Funding\Api4\Action\FundingCase\FinishClearingAction
 *
 * @group headless
 */
final class FinishClearingActionTest extends AbstractFundingHeadlessTestCase {

  use FundingCaseTestFixturesTrait;

  public function test(): void {
    $this->addInternalFixtures();

    FundingCase::update(FALSE)
      ->addValue('status', 'ongoing')
      ->addWhere('id', '=', $this->permittedFundingCaseId)
      ->execute();

    ClearingProcessFixture::addFixture($this->applicationProcessId, [
      'status' => 'accepted',
      'is_review_content' => TRUE,
      'is_review_calculative' => TRUE,
    ]);

    $payoutProcess = PayoutProcessFixture::addFixture($this->permittedFundingCaseId);

    RequestTestUtil::mockInternalRequest($this->associatedContactId);

    $e = NULL;
    try {
      FundingCase::finishClearing()
        ->setId($this->permittedFundingCaseId)
        ->execute();
    }
    catch (UnauthorizedException $e) {
      static::assertSame(
        sprintf(
          'Finishing the clearing of funding case "%s" is not allowed.',
          $this->permittedFundingCase->getIdentifier()
        ),
        $e->getMessage()
      );
    }
    static::assertNotNull($e);

    FundingCaseContactRelationFixture::addContact(
      $this->associatedContactId,
      $this->permittedFundingCaseId,
      [ClearingProcessPermissions::REVIEW_CALCULATIVE],
    );

    AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $this->fundingCaseTypeId,
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::PAYBACK_CLAIM_TEMPLATE],
    );

    $result = FundingCase::finishClearing()
      ->setId($this->permittedFundingCaseId)
      ->execute();

    static::assertSame('cleared', $result['status']);
    static::assertSame('closed', FundingPayoutProcess::get(FALSE)
      ->addSelect('status')
      ->addWhere('id', '=', $payoutProcess->getId())
      ->execute()
      ->single()['status']
    );
  }

}
