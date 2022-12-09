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
use Civi\Funding\Util\SessionTestUtil;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingCaseType
 * @covers \Civi\Funding\Api4\Action\FundingCaseType\GetByFundingProgramIdAction
 */
final class FundingCaseTypeTest extends AbstractFundingHeadlessTestCase {

  use FundingCaseTypeFixturesTrait;

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testGetByFundingProgramId(): void {
    SessionTestUtil::mockRemoteRequestSession((string) $this->permittedContactId);
    static::assertCount(1, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramId)
      ->execute());

    static::assertCount(0, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramIdWithoutFundingCaseType)
      ->execute());

    SessionTestUtil::mockRemoteRequestSession((string) $this->notPermittedContactId);
    static::assertCount(0, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramId)
      ->execute());
  }

}
