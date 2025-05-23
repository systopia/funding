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

namespace Civi\Funding\Upgrade;

use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\FundingProgramContactRelation;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0013
 *
 * @group headless
 */
final class Upgrader0013Test extends AbstractFundingHeadlessTestCase {

  use ArraySubsetAsserts;

  public function testExecute(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    FundingProgramContactRelationFixture::addFixture($fundingProgram->getId(), 'ContactRelationship', [
      'relationshipTypeId' => 2,
      'contactId' => 3,
    ], ['program_permission']);

    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();
    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id']
    );

    FundingCaseContactRelationFixture::addFixture($fundingCase->getId(), 'ContactRelationship', [
      'relationshipTypeId' => 4,
      'contactId' => 5,
    ], ['case_permission']);

    /** @var \Civi\Funding\Upgrade\Upgrader0013 $upgrader */
    $upgrader = \Civi::service(Upgrader0013::class);

    // Should migrate contact relations of type 'ContactTypeRelationship'.
    $upgrader->execute(new \Log_null('test'));

    static::assertArraySubset([
      'type' => 'ContactRelationships',
      'funding_program_id' => $fundingProgram->getId(),
      'permissions' => ['program_permission'],
      'properties' => [
        'relationships' => [
          [
            'relationshipTypeId' => 2,
            'contactId' => 3,
          ],
        ],
      ],
    ], FundingProgramContactRelation::get(FALSE)->execute()->single());

    static::assertArraySubset([
      'type' => 'ContactRelationships',
      'funding_case_id' => $fundingCase->getId(),
      'permissions' => ['case_permission'],
      'properties' => [
        'relationships' => [
          [
            'relationshipTypeId' => 4,
            'contactId' => 5,
          ],
        ],
      ],
    ], FundingCaseContactRelation::get(FALSE)->execute()->single());
  }

}
