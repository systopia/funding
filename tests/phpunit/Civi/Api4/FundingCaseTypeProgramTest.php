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

namespace Civi\Api4;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingCaseTypeProgramFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;

/**
 * @covers \Civi\Api4\FundingCaseTypeProgram
 *
 * @group headless
 */
final class FundingCaseTypeProgramTest extends AbstractFundingHeadlessTestCase {

  protected function setUp(): void {
    parent::setUp();
  }

  public function testExtractStrings(): void {
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseTypeProgram = FundingCaseTypeProgramFixture::addFixture(
      $fundingCaseType->getId(),
      $fundingProgram->getId()
    );

    $e = NULL;
    try {
      FundingCaseTypeProgram::extractStrings()->execute();
    }
    catch (UnauthorizedException $e) {
      // @ignoreException
    }
    static::assertNotNull($e);

    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);

    static::assertCount(0, FundingCaseTypeProgram::extractStrings()
      ->setWhere([['id', '=', $fundingCaseTypeProgram['id'] + 1]])
      ->execute());

    static::assertEquals($fundingCaseTypeProgram['id'], FundingCaseTypeProgram::extractStrings()
      ->setWhere([['id', '=', $fundingCaseTypeProgram['id']]])
      ->execute()->single()['id']);

    $stringTranslations = FundingFormStringTranslation::get()
      ->addWhere('funding_case_type_id', '=', $fundingCaseType->getId())
      ->addWhere('funding_program_id', '=', $fundingProgram->getId())
      ->execute();
    static::assertNotEmpty($stringTranslations);
    foreach ($stringTranslations as $stringTranslation) {
      static::assertSame($stringTranslation['msg_text'], $stringTranslation['new_text']);
    }
  }

}
