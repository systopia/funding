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
 * @covers \Civi\Funding\EventSubscriber\Remote\FundingCaseDAOGetSubscriber
 * @covers \Civi\Funding\EventSubscriber\Remote\FundingCasePermissionsGetSubscriber
 */
final class RemoteFundingCaseTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  private int $associatedContactId;

  private int $associatedContactIdNoPermissions;

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
    // Contact is directly associated
    $permittedAssociatedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->associatedContactId)
      ->execute();
    static::assertSame(1, $permittedAssociatedResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedAssociatedResult->first()['id']);
    static::assertSame(['foo', 'bar'], $permittedAssociatedResult->first()['permissions']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_foo']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_bar']);

    // Contact has an a-b-relationship with an associated contact
    $permittedABResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->relatedABContactId)
      ->execute();
    static::assertSame(1, $permittedABResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedABResult->first()['id']);
    static::assertSame(['c', 'd'], $permittedABResult->first()['permissions']);
    static::assertTrue($permittedABResult->first()['PERM_c']);
    static::assertTrue($permittedABResult->first()['PERM_d']);

    // Contact has an b-a-relationship with an associated contact
    $permittedBAResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->relatedBAContactId)
      ->execute();
    static::assertSame(1, $permittedBAResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedBAResult->first()['id']);
    static::assertSame(['c', 'd'], $permittedBAResult->first()['permissions']);
    static::assertTrue($permittedBAResult->first()['PERM_c']);
    static::assertTrue($permittedBAResult->first()['PERM_d']);

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

    $permittedRelationshipTypeId = RelationshipType::create()
      ->setValues([
        'name_a_b' => 'permitted',
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

    $this->associatedContactIdNoPermissions = Contact::create()
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Associated No Permissions',
        'last_name' => 'User',
      ])->execute()->first()['id'];

    $contactRelationId = FundingCaseContactRelation::create()
      ->setValues([
        'funding_case_id' => $this->permittedFundingCaseId,
        'entity_table' => 'civicrm_contact',
        'entity_id' => $this->associatedContactIdNoPermissions,
        'permissions' => NULL,
      ])->execute()->first()['id'];

    $this->associatedContactId = Contact::create()
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Associated',
        'last_name' => 'User',
      ])->execute()->first()['id'];

    FundingCaseContactRelation::create()
      ->setValues([
        'funding_case_id' => $this->permittedFundingCaseId,
        'entity_table' => 'civicrm_contact',
        'entity_id' => $this->associatedContactId,
        'permissions' => ['foo', 'bar'],
      ])->execute();

    FundingCaseContactRelation::create()
      ->setValues([
        'funding_case_id' => $this->permittedFundingCaseId,
        'entity_table' => 'civicrm_relationship_type',
        'entity_id' => $permittedRelationshipTypeId,
        'parent_id' => $contactRelationId,
        'permissions' => ['c', 'd'],
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
        'contact_id_a' => $this->associatedContactIdNoPermissions,
        'contact_id_b' => $this->relatedABContactId,
        'relationship_type_id' => $permittedRelationshipTypeId,
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
        'contact_id_b' => $this->associatedContactIdNoPermissions,
        'relationship_type_id' => $permittedRelationshipTypeId,
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
        'contact_id_b' => $this->associatedContactIdNoPermissions,
        'relationship_type_id' => $notPermittedRelationshipTypeId,
      ])->execute();
  }

}
