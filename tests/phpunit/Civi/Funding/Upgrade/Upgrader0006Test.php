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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\FundingProgramContactRelation;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Upgrade\Upgrader0006;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0006
 *
 * @group headless
 */
final class Upgrader0006Test extends AbstractFundingHeadlessTestCase {

  use ArraySubsetAsserts;

  public function testExecute(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    FundingProgramContactRelationFixture::addFixture($fundingProgram->getId(), 'ContactTypeRelationship', [
      'relationshipTypeId' => 1,
      'contactTypeId' => 2,
    ], ['program_permission']);

    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();
    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id']
    );

    FundingCaseContactRelationFixture::addFixture($fundingCase->getId(), 'ContactTypeRelationship', [
      'relationshipTypeId' => 3,
      'contactTypeId' => 4,
    ], ['case_permission']);

    /** @var \Civi\Funding\Upgrade\Upgrader0006 $upgrader */
    $upgrader = \Civi::service(Upgrader0006::class);

    // Should migrate contact relations of type 'ContactTypeRelationship'.
    $upgrader->execute(new \Log_null('test'));

    static::assertArraySubset([
      'type' => 'Relationship',
      'funding_program_id' => $fundingProgram->getId(),
      'permissions' => ['program_permission'],
      'properties' => [
        'relationshipTypeIds' => [1],
        'contactTypeIds' => [2],
        'groupIds' => [],
      ],
    ], FundingProgramContactRelation::get(FALSE)->execute()->single());

    static::assertArraySubset([
      'type' => 'Relationship',
      'funding_case_id' => $fundingCase->getId(),
      'permissions' => ['case_permission'],
      'properties' => [
        'relationshipTypeIds' => [3],
        'contactTypeIds' => [4],
        'groupIds' => [],
      ],
    ], FundingCaseContactRelation::get(FALSE)->execute()->single());
  }

}
