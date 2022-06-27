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

use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingCase
 * @covers \Civi\Funding\EventSubscriber\RemoteFundingCaseDAOGetSubscriber
 * @covers \Civi\Funding\EventSubscriber\RemoteFundingCasePermissionsSubscriber
 */
final class RemoteFundingCaseTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  private int $associatedContactId;

  private int $relatedABContactId;

  private int $relatedBAContactId;

  private int $permittedFundingCaseId;

  private int $notPermittedContactId;

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
    $permittedAssociatedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->associatedContactId)
      ->execute();
    static::assertSame(1, $permittedAssociatedResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedAssociatedResult->first()['id']);
    static::assertSame(['a', 'b', 'c'], $permittedAssociatedResult->first()['permissions']);

    $permittedABResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->relatedABContactId)
      ->execute();
    static::assertSame(1, $permittedABResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedABResult->first()['id']);
    static::assertSame(['a', 'b'], $permittedABResult->first()['permissions']);

    $permittedBAResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->relatedBAContactId)
      ->execute();
    static::assertSame(1, $permittedBAResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedBAResult->first()['id']);
    static::assertSame(['a', 'c'], $permittedBAResult->first()['permissions']);

    $notPermittedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->notPermittedContactId)
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);
  }

  private function addFixtures(): void {
    $fundingProgramId = FundingProgram::create()
      ->setValues([
        'title' => 'Foo',
        'start_date' => '2022-10-22',
        'end_date' => '2023-10-22',
        'requests_start_date' => '2022-06-22',
        'requests_end_date' => '2022-12-31',
        'currency' => '€',
      ])->execute()->first()['id'];

    $fundingCaseTypeId = FundingCaseType::create()
      ->setValues([
        'title' => 'Test Case Type',
        'name' => 'TestCaseType',
      ])->execute()->first()['id'];

    $recipientContactId = Contact::create()
      ->setValues([
        'contact_type' => 'Organization',
        'legal_name' => 'Recipient Organization',
      ])->execute()->first()['id'];

    $this->permittedFundingCaseId = FundingCase::create()
      ->setValues([
        'funding_program_id' => $fundingProgramId,
        'funding_case_type_id' => $fundingCaseTypeId,
        'status' => 'open',
        'creation_date' => '2022-06-23 10:00:00',
        'modification_date' => '2022-06-24 10:00:00',
        'recipient_contact_id' => $recipientContactId,
      ])->execute()->first()['id'];

    FundingCase::create()
      ->setValues([
        'funding_program_id' => $fundingProgramId,
        'funding_case_type_id' => $fundingCaseTypeId,
        'status' => 'open',
        'creation_date' => '2022-06-23 10:00:00',
        'modification_date' => '2022-06-24 10:00:00',
        'recipient_contact_id' => $recipientContactId,
      ])->execute();

    $permittedABRelationshipTypeId = RelationshipType::create()
      ->setValues([
        'name_a_b' => 'permitted',
        'name_b_a' => 'not permitted',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    $permittedBARelationshipTypeId = RelationshipType::create()
      ->setValues([
        'name_a_b' => 'not permitted',
        'name_b_a' => 'permitted',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    $notPermittedRelationshipTypeId = RelationshipType::create()
      ->setValues([
        'name_b_a' => 'foo',
        'name_a_b' => 'bar',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    $this->associatedContactId = Contact::create()
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Associated',
        'last_name' => 'User',
      ])->execute()->first()['id'];

    FundingCaseContact::create()
      ->setValues([
        'funding_case_id' => $this->permittedFundingCaseId,
        'contact_id' => $this->associatedContactId,
        'relationship_type_id' => $permittedABRelationshipTypeId,
        'relationship_direction' => 'a_b',
        'permissions' => ['a', 'b'],
      ])->execute();

    FundingCaseContact::create()
      ->setValues([
        'funding_case_id' => $this->permittedFundingCaseId,
        'contact_id' => $this->associatedContactId,
        'relationship_type_id' => $permittedBARelationshipTypeId,
        'relationship_direction' => 'b_a',
        'permissions' => ['a', 'c'],
      ])->execute();

    $this->relatedABContactId = Contact::create()
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'RelatedAB',
        'last_name' => 'User',
      ])
      ->execute()->first()['id'];

    Relationship::create()
      ->setValues([
        'contact_id_a' => $this->associatedContactId,
        'contact_id_b' => $this->relatedABContactId,
        'relationship_type_id' => $permittedABRelationshipTypeId,
      ])->execute();

    $this->relatedBAContactId = Contact::create()
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'RelatedBA',
        'last_name' => 'User',
      ])
      ->execute()->first()['id'];

    Relationship::create()
      ->setValues([
        'contact_id_a' => $this->relatedBAContactId,
        'contact_id_b' => $this->associatedContactId,
        'relationship_type_id' => $permittedBARelationshipTypeId,
      ])->execute();

    $this->notPermittedContactId = Contact::create()
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'NotPermitted',
        'last_name' => 'User',
      ])
      ->execute()->first()['id'];

    Relationship::create()
      ->setValues([
        'contact_id_a' => $this->notPermittedContactId,
        'contact_id_b' => $this->associatedContactId,
        'relationship_type_id' => $notPermittedRelationshipTypeId,
      ])->execute();
  }

}
