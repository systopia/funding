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

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Traits\FundingCaseTestFixturesTrait;
use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingCaseTypeProgramFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Mock\Form\FundingCaseType\TestJsonSchema;
use Civi\Funding\Mock\Form\FundingCaseType\TestUiSchema;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingCase
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction
 * @covers \Civi\Funding\Api4\Action\Remote\DAOGetAction
 * @covers \Civi\Funding\EventSubscriber\Remote\FundingCaseDAOGetSubscriber
 */
final class RemoteFundingCaseTest extends AbstractRemoteFundingHeadlessTestCase {

  use FundingCaseTestFixturesTrait;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(NewApplicationFormActionTrait::class);
  }

  public function testGetNewApplicationForm(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture(['title' => 'Foo']);
    $fundingCaseType = FundingCaseTypeFixture::addFixture();

    ClockMock::withClockMock($fundingProgram->getRequestsStartDate()->getTimestamp());

    // Funding case type and funding program not related
    $e = NULL;
    try {
      RemoteFundingCase::getNewApplicationForm()
        ->setRemoteContactId((string) $contact['id'])
        ->setFundingCaseTypeId($fundingCaseType->getId())
        ->setFundingProgramId($fundingProgram->getId())
        ->execute();
    }
    catch (FundingException $e) {
      static::assertSame('Funding program and funding case type are not related', $e->getMessage());
    }
    static::assertNotNull($e);

    FundingCaseTypeProgramFixture::addFixture($fundingCaseType->getId(), $fundingProgram->getId());

    // No "application_create" permission
    FundingProgramContactRelationFixture::addContact($contact['id'], $fundingProgram->getId(), ['application_test']);
    $e = NULL;
    try {
      RemoteFundingCase::getNewApplicationForm()
        ->setRemoteContactId((string) $contact['id'])
        ->setFundingCaseTypeId($fundingCaseType->getId())
        ->setFundingProgramId($fundingProgram->getId())
        ->execute();
    }
    catch (UnauthorizedException $e) {
      static::assertSame('Required permission is missing', $e->getMessage());
    }
    static::assertNotNull($e);

    // Creating application allowed
    FundingProgramContactRelationFixture::addContact($contact['id'], $fundingProgram->getId(), ['application_create']);
    $values = RemoteFundingCase::getNewApplicationForm()
      ->setRemoteContactId((string) $contact['id'])
      ->setFundingCaseTypeId($fundingCaseType->getId())
      ->setFundingProgramId($fundingProgram->getId())
      ->execute()
      ->getArrayCopy();

    static::assertEquals(['jsonSchema', 'uiSchema', 'data'], array_keys($values));
    static::assertInstanceOf(TestJsonSchema::class, $values['jsonSchema']);
    static::assertInstanceOf(TestUiSchema::class, $values['uiSchema']);
    static::assertIsArray($values['data']);

    // Current date is before requests start date
    ClockMock::withClockMock($fundingProgram->getRequestsStartDate()->getTimestamp() - 86400);
    $e = NULL;
    try {
      RemoteFundingCase::getNewApplicationForm()
        ->setRemoteContactId((string) $contact['id'])
        ->setFundingCaseTypeId($fundingCaseType->getId())
        ->setFundingProgramId($fundingProgram->getId())
        ->execute();
    }
    catch (FundingException $e) {
      static::assertSame('Funding program does not allow applications before 2022-06-22', $e->getMessage());
    }
    static::assertNotNull($e);

    // Current date is after requests end date
    ClockMock::withClockMock($fundingProgram->getRequestsEndDate()->getTimestamp() + 86400);
    $e = NULL;
    try {
      RemoteFundingCase::getNewApplicationForm()
        ->setRemoteContactId((string) $contact['id'])
        ->setFundingCaseTypeId($fundingCaseType->getId())
        ->setFundingProgramId($fundingProgram->getId())
        ->execute();
    }
    catch (FundingException $e) {
      static::assertSame('Funding program does not allow applications after 2022-12-31', $e->getMessage());
    }
    static::assertNotNull($e);
  }

  public function testPermissions(): void {
    $this->addRemoteFixtures();

    // Contact is directly associated
    $permittedAssociatedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->associatedContactId)
      ->execute();
    static::assertSame(1, $permittedAssociatedResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedAssociatedResult->first()['id']);
    static::assertSame(['application_foo', 'application_bar'], $permittedAssociatedResult->first()['permissions']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_application_foo']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_application_bar']);
    static::assertArrayNotHasKey('PERM_review_baz', $permittedAssociatedResult->first());

    // Contact has an a-b-relationship with an associated contact
    $permittedABResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->relatedABContactId)
      ->execute();
    static::assertSame(1, $permittedABResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedABResult->first()['id']);
    static::assertSame(['application_c', 'application_d'], $permittedABResult->first()['permissions']);
    static::assertTrue($permittedABResult->first()['PERM_application_c']);
    static::assertTrue($permittedABResult->first()['PERM_application_d']);
    static::assertArrayNotHasKey('PERM_review_e', $permittedABResult->first());

    // Contact has an b-a-relationship with an associated contact
    $permittedBAResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->relatedBAContactId)
      ->execute();
    static::assertSame(1, $permittedBAResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedBAResult->first()['id']);
    static::assertSame(['application_c', 'application_d'], $permittedBAResult->first()['permissions']);
    static::assertTrue($permittedBAResult->first()['PERM_application_c']);
    static::assertTrue($permittedBAResult->first()['PERM_application_d']);
    static::assertArrayNotHasKey('PERM_review_e', $permittedBAResult->first());

    // Contact has a not permitted relationship with an associated contact
    $notPermittedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->notPermittedContactId)
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact is directly associated, but has no permissions set
    $permittedAssociatedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->associatedContactIdNoPermissions)
      ->execute();
    static::assertSame(0, $permittedAssociatedResult->rowCount);
  }

}
