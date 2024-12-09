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

namespace tests\phpunit\Civi\Funding\Upgrade;

use Civi\Api4\FundingCase;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\ApplicationProcessBundleFixture;
use Civi\Funding\Fixtures\AttachmentFixture;
use Civi\Funding\Fixtures\PayoutProcessFixture;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\Upgrade\Upgrader0009;
use CRM_Funding_ExtensionUtil as E;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0009
 *
 * @group headless
 */
final class Upgrader0009Test extends AbstractFundingHeadlessTestCase {

  public function testExecute(): void {
    $applicationProcessBundle = ApplicationProcessBundleFixture::create([], [
      'status' => FundingCaseStatus::WITHDRAWN,
      'amount_approved' => 0.1,
    ]);
    PayoutProcessFixture::addFixture($applicationProcessBundle->getFundingCase()->getId());

    AttachmentFixture::addFixture(
      'civicrm_funding_case_type',
      $applicationProcessBundle->getFundingCaseType()->getId(),
      E::path('tests/phpunit/resources/FundingCaseDocumentTemplate.docx'),
      ['file_type_id:name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE],
    );

    /** @var \Civi\Funding\Upgrade\Upgrader0009 $upgrader */
    $upgrader = \Civi::service(Upgrader0009::class);
    $upgrader->execute(new \Log_null('test'));

    $fundingCaseValues = FundingCase::get(FALSE)
      ->addSelect('amount_approved', 'transfer_contract_uri')
      ->addWhere('id', '=', $applicationProcessBundle->getFundingCase()->getId())
      ->execute()
      ->single();
    static::assertSame(0.0, $fundingCaseValues['amount_approved']);
    static::assertNotNull($fundingCaseValues['transfer_contract_uri']);
  }

}
