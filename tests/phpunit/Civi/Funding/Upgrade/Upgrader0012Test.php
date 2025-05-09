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

use Civi\Api4\FundingRecipientContactRelation;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\FundingRecipientContactRelationFixture;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0012
 *
 * @group headless
 */
final class Upgrader0012Test extends AbstractFundingHeadlessTestCase {

  use ArraySubsetAsserts;

  public function testExecuteRelationshipType(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    FundingRecipientContactRelationFixture::addFixture($fundingProgram->getId(), 'RelationshipType', [
      'relationshipTypeId' => 1,
    ]);

    /** @var \Civi\Funding\Upgrade\Upgrader0012 $upgrader */
    $upgrader = \Civi::service(Upgrader0012::class);
    $upgrader->execute(new \Log_null('test'));

    static::assertArraySubset([
      'type' => 'Relationship',
      'funding_program_id' => $fundingProgram->getId(),
      'properties' => [
        'relationshipTypeIds' => [1],
        'contactTypeIds' => [],
        'groupIds' => [],
      ],
    ], FundingRecipientContactRelation::get(FALSE)->execute()->single());
  }

  public function testExecuteContactTypeAndRelationshipType(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    FundingRecipientContactRelationFixture::addFixture($fundingProgram->getId(), 'ContactTypeAndRelationshipType', [
      'relationshipTypeId' => 1,
      'contactTypeId' => 2,
    ]);

    /** @var \Civi\Funding\Upgrade\Upgrader0012 $upgrader */
    $upgrader = \Civi::service(Upgrader0012::class);
    $upgrader->execute(new \Log_null('test'));

    static::assertArraySubset([
      'type' => 'Relationship',
      'funding_program_id' => $fundingProgram->getId(),
      'properties' => [
        'relationshipTypeIds' => [1],
        'contactTypeIds' => [2],
        'groupIds' => [],
      ],
    ], FundingRecipientContactRelation::get(FALSE)->execute()->single());
  }

}
