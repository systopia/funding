<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingCaseTypeProgramFixture;
use Civi\Funding\Fixtures\FundingNewCasePermissionsFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\FundingRecipientContactRelationFixture;

/**
 * @group headless
 * @covers \Civi\Funding\FundingProgram\Api4\ActionHandler\CloneHandler
 * @covers \Civi\Funding\Api4\Action\FundingProgram\CloneAction
 */
final class FundingProgramCloneTest extends AbstractFundingHeadlessTestCase {

  public function testClone(): void {
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);

    $sourceProgram = FundingProgramFixture::addFixture([
      'title' => 'Source Program',
      'abbreviation' => 'SP',
      'identifier_prefix' => 'SP',
      'start_date' => '2026-01-01',
      'end_date' => '2026-12-31',
      'requests_start_date' => '2026-01-01',
      'requests_end_date' => '2026-06-30',
    ]);
    $sourceId = $sourceProgram->getId();

    $caseType = FundingCaseTypeFixture::addFixture([
      'name' => 'test_case_type',
      'label' => 'Test Case Type',
      'title' => 'Test Case Type',
      'abbreviation' => 'TCT',
      'is_combined_application' => FALSE,
    ]);
    FundingCaseTypeProgramFixture::addFixture($caseType->getId(), $sourceId);

    FundingProgramContactRelationFixture::addFixture($sourceId, 'Contact', ['contactId' => 1], ['view']);
    FundingRecipientContactRelationFixture::addFixture($sourceId, 'Relationship', ['relationshipTypeIds' => [1]]);
    FundingNewCasePermissionsFixture::addFixture($sourceId, 'Contact', ['contactId' => 1], ['view']);

    FundingFormStringTranslation::create(FALSE)
      ->setValues([
        'funding_program_id' => $sourceId,
        'funding_case_type_id' => $caseType->getId(),
        'msg_text' => 'test_string',
        'new_text' => 'test_translation',
      ])
      ->execute();

    $result = FundingProgram::clone(FALSE)
      ->addWhere('id', '=', $sourceId)
      ->execute();

    static::assertCount(1, $result);
    $newProgram = $result->first();
    $newId = $newProgram['id'];

    static::assertNotEquals($sourceId, $newId);
    static::assertSame('Copy of Source Program', $newProgram['title']);
    static::assertSame('SP_copy', $newProgram['abbreviation']);

    $related = FundingCaseTypeProgram::get(FALSE)
      ->addWhere('funding_program_id', '=', $newId)
      ->execute();
    static::assertSame(1, $related->rowCount);
    static::assertSame($caseType->getId(), $related->first()['funding_case_type_id']);

    static::assertSame(1, FundingProgramContactRelation::get(FALSE)
      ->addWhere('funding_program_id', '=', $newId)
      ->execute()->rowCount);

    static::assertSame(1, FundingRecipientContactRelation::get(FALSE)
      ->addWhere('funding_program_id', '=', $newId)
      ->execute()->rowCount);

    static::assertSame(1, FundingNewCasePermissions::get(FALSE)
      ->addWhere('funding_program_id', '=', $newId)
      ->execute()->rowCount);

    static::assertSame(1, FundingFormStringTranslation::get(FALSE)
      ->addWhere('funding_program_id', '=', $newId)
      ->execute()->rowCount);

    $result2 = FundingProgram::clone(FALSE)
      ->addWhere('id', '=', $sourceId)
      ->execute();
    $newProgram2 = $result2->first();
    static::assertSame('Copy of Source Program 2', $newProgram2['title']);
    static::assertSame('SP_copy_2', $newProgram2['abbreviation']);

    $result3 = FundingProgram::clone(FALSE)
      ->addWhere('id', '=', $sourceId)
      ->setValues([
        'title' => 'Custom Title',
        'abbreviation' => 'CT',
      ])
      ->execute();
    $newProgram3 = $result3->first();
    static::assertSame('Custom Title', $newProgram3['title']);
    static::assertSame('CT', $newProgram3['abbreviation']);
  }

  public function testCloneThrowsUnauthorizedException(): void {
    $this->expectException(UnauthorizedException::class);
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM]);
    $sourceProgram = FundingProgramFixture::addFixture(['title' => 'Source']);
    FundingProgram::clone()
      ->addWhere('id', '=', $sourceProgram->getId())
      ->execute();
  }

}
