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
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Util\SessionTestUtil;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingProgram
 * @covers \Civi\Funding\Api4\Action\FundingProgram\GetAction
 * @covers \Civi\Funding\EventSubscriber\FundingProgram\FundingProgramPermissionsGetSubscriber
 */
final class FundingProgramTest extends AbstractFundingHeadlessTestCase {

  use FundingProgramTestFixturesTrait;

  protected int $permittedIndividualId;

  protected int $notPermittedContactId;

  protected int $permittedOrganizationIdNoPermissions;

  protected int $permittedOrganizationId;

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testPermissionsInternal(): void {

    // Admin gets view permissions for all programs.
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ADMINISTER_FUNDING]);
    $adminResult = FundingProgram::get()
      ->execute();
    static::assertSame(2, $adminResult->rowCount);
    static::assertSame('Foo', $adminResult->first()['title']);
    static::assertSame(['view'], $adminResult->first()['permissions']);
    static::assertTrue($adminResult->first()['PERM_view']);
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ACCESS_FUNDING]);

    // Contact has a permitted type
    SessionTestUtil::mockInternalRequestSession($this->permittedOrganizationId);
    $permittedOrganizationResult = FundingProgram::get()
      ->execute();
    static::assertSame(1, $permittedOrganizationResult->rowCount);
    static::assertSame('Foo', $permittedOrganizationResult->first()['title']);
    static::assertSame(['review_bar'], $permittedOrganizationResult->first()['permissions']);
    static::assertTrue($permittedOrganizationResult->first()['PERM_review_bar']);
    static::assertArrayNotHasKey('PERM_application_foo', $permittedOrganizationResult->first());

    // Contact has a relation that has a permitted type with a contact that has a permitted type
    SessionTestUtil::mockInternalRequestSession($this->permittedIndividualId);
    $permittedIndividualResult = FundingProgram::get()
      ->execute();
    static::assertSame(1, $permittedIndividualResult->rowCount);
    static::assertSame('Foo', $permittedIndividualResult->first()['title']);
    static::assertSame(['review_b'], $permittedIndividualResult->first()['permissions']);
    static::assertTrue($permittedIndividualResult->first()['PERM_review_b']);
    static::assertArrayNotHasKey('PERM_application_a', $permittedOrganizationResult->first());

    // Contact has a relation that has a not permitted type with a contact that has a permitted type
    SessionTestUtil::mockInternalRequestSession($this->notPermittedContactId);
    $notPermittedResult = FundingProgram::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact has a permitted type, but the relation has no permissions set
    SessionTestUtil::mockInternalRequestSession($this->permittedOrganizationIdNoPermissions);
    $notPermittedResult = FundingProgram::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Unrelated contact has access, if empty permissions are allowed.
    $unrelatedContact = ContactFixture::addIndividual();
    SessionTestUtil::mockInternalRequestSession($unrelatedContact['id']);
    static::assertCount(2, FundingProgram::get()
      ->setAllowEmptyRecordPermissions(TRUE)
      ->execute(),
    );
  }

  public function testPermissionsRemote(): void {
    // Contact has a permitted type
    SessionTestUtil::mockRemoteRequestSession((string) $this->permittedOrganizationId);
    $permittedOrganizationResult = FundingProgram::get()
      ->execute();
    static::assertSame(1, $permittedOrganizationResult->rowCount);
    static::assertSame('Foo', $permittedOrganizationResult->first()['title']);
    static::assertSame(['application_foo'], $permittedOrganizationResult->first()['permissions']);
    static::assertTrue($permittedOrganizationResult->first()['PERM_application_foo']);
    static::assertArrayNotHasKey('PERM_review_bar', $permittedOrganizationResult->first());

    // Contact has a relation that has a permitted type with a contact that has a permitted type
    SessionTestUtil::mockRemoteRequestSession((string) $this->permittedIndividualId);
    $permittedIndividualResult = FundingProgram::get()
      ->execute();
    static::assertSame(1, $permittedIndividualResult->rowCount);
    static::assertSame('Foo', $permittedIndividualResult->first()['title']);
    static::assertSame(['application_a'], $permittedIndividualResult->first()['permissions']);
    static::assertTrue($permittedIndividualResult->first()['PERM_application_a']);
    static::assertArrayNotHasKey('PERM_review_b', $permittedOrganizationResult->first());

    // Contact has a relation that has a not permitted type with a contact that has a permitted type
    SessionTestUtil::mockRemoteRequestSession((string) $this->notPermittedContactId);
    $notPermittedResult = FundingProgram::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact has a permitted type, but the relation has no permissions set
    SessionTestUtil::mockRemoteRequestSession((string) $this->permittedOrganizationIdNoPermissions);
    $notPermittedResult = FundingProgram::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);
  }

}
