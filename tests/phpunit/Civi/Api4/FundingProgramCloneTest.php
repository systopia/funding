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

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Permissions;

/**
 * @group headless
 * @covers \Civi\Funding\FundingProgram\Api4\ActionHandler\CloneHandler
 * @covers \Civi\Funding\Api4\Action\FundingProgram\CloneAction
 */
final class FundingProgramCloneTest extends AbstractFundingHeadlessTestCase {

  public function testClone(): void {
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);

    // 1. Create a source program
    $sourceProgram = FundingProgram::create(FALSE)
      ->setValues([
        'title' => 'Source Program',
        'abbreviation' => 'SP',
        'identifier_prefix' => 'SP',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'requests_start_date' => '2026-01-01',
        'requests_end_date' => '2026-06-30',
      ])
      ->execute()
      ->first();
    $sourceId = $sourceProgram['id'];

    // 2. Add some related entities
    // FundingCaseTypeProgram
    $caseType = FundingCaseType::create(FALSE)
      ->setValues([
        'name' => 'test_case_type',
        'label' => 'Test Case Type',
        'title' => 'Test Case Type',
        'abbreviation' => 'TCT',
        'is_combined_application' => FALSE,
      ])
      ->execute()->first();
    FundingCaseTypeProgram::create(FALSE)
      ->setValues(['funding_program_id' => $sourceId, 'funding_case_type_id' => $caseType['id']])
      ->execute();

    // FundingProgramContactRelation
    FundingProgramContactRelation::create(FALSE)
      ->setValues([
        'funding_program_id' => $sourceId,
        'type' => 'Contact',
        'properties' => ['contactId' => 1],
        'permissions' => ['view'],
      ])
      ->execute();

    // FundingRecipientContactRelation
    FundingRecipientContactRelation::create(FALSE)
      ->setValues([
        'funding_program_id' => $sourceId,
        'type' => 'Relationship',
        'properties' => ['relationshipTypeIds' => [1]],
      ])
      ->execute();

    // FundingNewCasePermissions
    FundingNewCasePermissions::create(FALSE)
      ->setValues([
        'funding_program_id' => $sourceId,
        'type' => 'Contact',
        'properties' => ['contactId' => 1],
        'permissions' => ['view'],
      ])
      ->execute();

    // FundingFormStringTranslation
    FundingFormStringTranslation::create(FALSE)
      ->setValues([
        'funding_program_id' => $sourceId,
        'funding_case_type_id' => $caseType['id'],
        'msg_text' => 'test_string',
        'new_text' => 'test_translation',
      ])
      ->execute();

    // 3. Clone
    $result = FundingProgram::clone(FALSE)
      ->setId($sourceId)
      ->execute();

    static::assertCount(1, $result);
    $newProgram = $result->first();
    $newId = $newProgram['id'];

    static::assertNotEquals($sourceId, $newId);
    static::assertSame('Copy of Source Program', $newProgram['title']);
    static::assertSame('SP_copy', $newProgram['abbreviation']);

    // 4. Verify related entities were cloned
    $related = FundingCaseTypeProgram::get(FALSE)
      ->addWhere('funding_program_id', '=', $newId)
      ->execute();
    static::assertSame(1, $related->rowCount);
    static::assertSame($caseType['id'], $related->first()['funding_case_type_id']);

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

    // 5. Test uniqueness with second clone
    $result2 = FundingProgram::clone(FALSE)
      ->setId($sourceId)
      ->execute();
    $newProgram2 = $result2->first();
    static::assertSame('Copy of Source Program 2', $newProgram2['title']);
    static::assertSame('SP_copy_2', $newProgram2['abbreviation']);

    // 6. Test clone with custom values
    $result3 = FundingProgram::clone(FALSE)
      ->setId($sourceId)
      ->setValues([
        'title' => 'Custom Title',
        'abbreviation' => 'CT',
      ])
      ->execute();
    $newProgram3 = $result3->first();
    static::assertSame('Custom Title', $newProgram3['title']);
    static::assertSame('CT', $newProgram3['abbreviation']);
  }
}
