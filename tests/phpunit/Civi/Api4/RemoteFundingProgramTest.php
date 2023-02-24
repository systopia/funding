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
use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingProgram
 * @covers \Civi\Funding\Api4\Action\Remote\DAOGetAction
 * @covers \Civi\Funding\EventSubscriber\Remote\FundingProgramDAOGetSubscriber
 */
final class RemoteFundingProgramTest extends AbstractRemoteFundingHeadlessTestCase {

  use FundingProgramTestFixturesTrait;

  protected int $permittedIndividualId;

  protected int $notPermittedContactId;

  protected int $permittedOrganizationIdNoPermissions;

  protected int $permittedOrganizationId;

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testPermissions(): void {
    // Contact has a permitted type
    $permittedOrganizationResult = RemoteFundingProgram::get()
      ->setRemoteContactId((string) $this->permittedOrganizationId)
      ->execute();
    static::assertSame(1, $permittedOrganizationResult->rowCount);
    static::assertSame('Foo', $permittedOrganizationResult->first()['title']);
    static::assertSame(['application_foo'], $permittedOrganizationResult->first()['permissions']);
    static::assertTrue($permittedOrganizationResult->first()['PERM_application_foo']);
    static::assertArrayNotHasKey('PERM_review_bar', $permittedOrganizationResult->first());

    // Contact has a relation that has a permitted type with a contact that has a permitted type
    $permittedIndividualResult = RemoteFundingProgram::get()
      ->setRemoteContactId((string) $this->permittedIndividualId)
      ->execute();
    static::assertSame(1, $permittedIndividualResult->rowCount);
    static::assertSame('Foo', $permittedIndividualResult->first()['title']);
    static::assertSame(['application_a'], $permittedIndividualResult->first()['permissions']);
    static::assertTrue($permittedIndividualResult->first()['PERM_application_a']);
    static::assertArrayNotHasKey('PERM_review_b', $permittedOrganizationResult->first());

    // Contact has a relation that has a not permitted type with a contact that has a permitted type
    $notPermittedResult = RemoteFundingProgram::get()
      ->setRemoteContactId((string) $this->notPermittedContactId)
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact has a permitted type, but the relation has no permissions set
    $notPermittedResult = RemoteFundingProgram::get()
      ->setRemoteContactId((string) $this->permittedOrganizationIdNoPermissions)
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);
  }

}
