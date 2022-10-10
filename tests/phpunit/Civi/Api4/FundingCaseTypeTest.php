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
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingCaseType
 * @covers \Civi\Funding\Api4\Action\FundingCaseType\GetByFundingProgramIdAction
 */
final class FundingCaseTypeTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  use FundingCaseTypeFixturesTrait;

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testGetByFundingProgramId(): void {
    \CRM_Core_Session::singleton()->set('userID', $this->permittedContactId);
    static::assertCount(1, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramId)
      ->execute());

    static::assertCount(0, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramIdWithoutFundingCaseType)
      ->execute());

    \CRM_Core_Session::singleton()->set('userID', $this->notPermittedContactId);
    static::assertCount(0, FundingCaseType::getByFundingProgramId()
      ->setFundingProgramId($this->fundingProgramId)
      ->execute());
  }

}