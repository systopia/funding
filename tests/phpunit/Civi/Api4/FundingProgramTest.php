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

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Api4\Traits\FundingProgramTestFixturesTrait;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingProgram
 * @covers \Civi\Funding\Api4\Action\FundingProgram\GetAction
 * @covers \Civi\Funding\EventSubscriber\FundingProgram\FundingProgramPermissionsGetSubscriber
 */
final class FundingProgramTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  use FundingProgramTestFixturesTrait;

  protected int $permittedIndividualId;

  protected int $notPermittedContactId;

  protected int $permittedOrganizationIdNoPermissions;

  protected int $permittedOrganizationId;

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testPermissions(): void {
    // Contact has a permitted type
    \CRM_Core_Session::singleton()->set('userID', $this->permittedOrganizationId);
    $permittedOrganizationResult = FundingProgram::get()
      ->execute();
    static::assertSame(1, $permittedOrganizationResult->rowCount);
    static::assertSame('Foo', $permittedOrganizationResult->first()['title']);
    static::assertSame(['foo', 'bar'], $permittedOrganizationResult->first()['permissions']);
    static::assertTrue($permittedOrganizationResult->first()['PERM_foo']);
    static::assertTrue($permittedOrganizationResult->first()['PERM_bar']);

    // Contact has a relation that has a permitted type with a contact that has a permitted type
    \CRM_Core_Session::singleton()->set('userID', $this->permittedIndividualId);
    $permittedIndividualResult = FundingProgram::get()
      ->execute();
    static::assertSame(1, $permittedIndividualResult->rowCount);
    static::assertSame('Foo', $permittedIndividualResult->first()['title']);
    static::assertSame(['a', 'b'], $permittedIndividualResult->first()['permissions']);
    static::assertTrue($permittedIndividualResult->first()['PERM_a']);
    static::assertTrue($permittedIndividualResult->first()['PERM_b']);

    // Contact has a relation that has a not permitted type with a contact that has a permitted type
    \CRM_Core_Session::singleton()->set('userID', $this->notPermittedContactId);
    $notPermittedResult = FundingProgram::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact has a permitted type, but the relation has no permissions set
    \CRM_Core_Session::singleton()->set('userID', $this->permittedOrganizationIdNoPermissions);
    $notPermittedResult = FundingProgram::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);
  }

}
