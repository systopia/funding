<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

use Civi\Api4\Traits\FundingCaseTypeFixturesTrait;
use Civi\Funding\AbstractFundingHeadlessTestCase;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingCaseType
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCaseType\GetByFundingProgramIdAction
 * @covers \Civi\Funding\EventSubscriber\Remote\FundingCaseTypeGetByFundingProgramIdSubscriber
 */
final class RemoteFundingCaseTypeTest extends AbstractFundingHeadlessTestCase {

  use FundingCaseTypeFixturesTrait;

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testGetByFundingProgramId(): void {
    static::assertCount(1, RemoteFundingCaseType::getByFundingProgramId()
      ->setRemoteContactId((string) $this->permittedContactId)
      ->setFundingProgramId($this->fundingProgramId)
      ->execute());

    static::assertCount(0, RemoteFundingCaseType::getByFundingProgramId()
      ->setRemoteContactId((string) $this->permittedContactId)
      ->setFundingProgramId($this->fundingProgramIdWithoutFundingCaseType)
      ->execute());

    static::assertCount(0, RemoteFundingCaseType::getByFundingProgramId()
      ->setRemoteContactId((string) $this->notPermittedContactId)
      ->setFundingProgramId($this->fundingProgramId)
      ->execute());
  }

}
