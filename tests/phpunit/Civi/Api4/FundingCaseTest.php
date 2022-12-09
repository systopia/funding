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

use Civi\Api4\Traits\FundingCaseTestFixturesTrait;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Util\SessionTestUtil;

/**
 * @group headless
 *
 * @covers \Civi\Api4\FundingCase
 * @covers \Civi\Funding\Api4\Action\FundingCase\GetAction
 * @covers \Civi\Funding\EventSubscriber\FundingCase\FundingCasePermissionsGetSubscriber
 */
final class FundingCaseTest extends AbstractFundingHeadlessTestCase {

  use FundingCaseTestFixturesTrait;

  public function testPermissionsInternal(): void {
    $this->addInternalFixtures();
    // Contact is directly associated
    SessionTestUtil::mockInternalRequestSession($this->associatedContactId);
    $permittedAssociatedResult = FundingCase::get()->execute();
    static::assertSame(1, $permittedAssociatedResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedAssociatedResult->first()['id']);
    static::assertSame(['review_baz'], $permittedAssociatedResult->first()['permissions']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_review_baz']);

    // Contact has an a-b-relationship with an associated contact
    SessionTestUtil::mockInternalRequestSession($this->relatedABContactId);
    $permittedABResult = FundingCase::get()->execute();
    static::assertSame(1, $permittedABResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedABResult->first()['id']);
    static::assertSame(['review_e'], $permittedABResult->first()['permissions']);
    static::assertTrue($permittedABResult->first()['PERM_review_e']);

    // Contact has an b-a-relationship with an associated contact
    SessionTestUtil::mockInternalRequestSession($this->relatedBAContactId);
    $permittedBAResult = FundingCase::get()
      ->execute();
    static::assertSame(1, $permittedBAResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedBAResult->first()['id']);
    static::assertSame(['review_e'], $permittedBAResult->first()['permissions']);
    static::assertTrue($permittedBAResult->first()['PERM_review_e']);

    // Contact has a not permitted relationship with an associated contact
    SessionTestUtil::mockInternalRequestSession($this->notPermittedContactId);
    $notPermittedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact is directly associated, but has no permissions set
    SessionTestUtil::mockInternalRequestSession($this->associatedContactIdNoPermissions);
    $permittedAssociatedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $permittedAssociatedResult->rowCount);

    // Contact is directly associated, but has application and review permissions
    SessionTestUtil::mockInternalRequestSession($this->associatedContactIdApplicationAndReview);
    $permittedAssociatedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $permittedAssociatedResult->rowCount);
  }

  public function testPermissionsRemote(): void {
    $this->addRemoteFixtures();

    // Contact is directly associated
    SessionTestUtil::mockRemoteRequestSession((string) $this->associatedContactId);
    $permittedAssociatedResult = FundingCase::get()
      ->execute();
    static::assertSame(1, $permittedAssociatedResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedAssociatedResult->first()['id']);
    static::assertSame(['application_foo', 'application_bar'], $permittedAssociatedResult->first()['permissions']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_application_foo']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_application_bar']);
    static::assertArrayNotHasKey('PERM_review_baz', $permittedAssociatedResult->first());

    // Contact has an a-b-relationship with an associated contact
    SessionTestUtil::mockRemoteRequestSession((string) $this->relatedABContactId);
    $permittedABResult = FundingCase::get()
      ->execute();
    static::assertSame(1, $permittedABResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedABResult->first()['id']);
    static::assertSame(['application_c', 'application_d'], $permittedABResult->first()['permissions']);
    static::assertTrue($permittedABResult->first()['PERM_application_c']);
    static::assertTrue($permittedABResult->first()['PERM_application_d']);
    static::assertArrayNotHasKey('PERM_review_e', $permittedABResult->first());

    // Contact has an b-a-relationship with an associated contact
    SessionTestUtil::mockRemoteRequestSession((string) $this->relatedBAContactId);
    $permittedBAResult = FundingCase::get()
      ->execute();
    static::assertSame(1, $permittedBAResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedBAResult->first()['id']);
    static::assertSame(['application_c', 'application_d'], $permittedBAResult->first()['permissions']);
    static::assertTrue($permittedBAResult->first()['PERM_application_c']);
    static::assertTrue($permittedBAResult->first()['PERM_application_d']);
    static::assertArrayNotHasKey('PERM_review_e', $permittedBAResult->first());

    // Contact has a not permitted relationship with an associated contact
    SessionTestUtil::mockRemoteRequestSession((string) $this->notPermittedContactId);
    $notPermittedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact is directly associated, but has no permissions set
    SessionTestUtil::mockRemoteRequestSession((string) $this->associatedContactIdNoPermissions);
    $permittedAssociatedResult = FundingCase::get()
      ->execute();
    static::assertSame(0, $permittedAssociatedResult->rowCount);
  }

}
