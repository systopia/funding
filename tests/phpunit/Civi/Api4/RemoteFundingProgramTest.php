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
 * @covers \Civi\Api4\RemoteFundingProgram
 * @covers \Civi\Funding\EventSubscriber\Remote\FundingProgramDAOGetSubscriber
 * @covers \Civi\Funding\EventSubscriber\Remote\FundingProgramPermissionsGetSubscriber
 */
final class RemoteFundingProgramTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  private const CONTACT_TYPE_ORGANIZATION_ID = 3;

  private int $permittedIndividualId;

  private int $notPermittedContactId;

  private int $permittedOrganizationIdNoPermissions;

  private int $permittedOrganizationId;

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
    $permittedOrganizationResult = RemoteFundingProgram::get()
      ->setRemoteContactId((string) $this->permittedOrganizationId)
      ->execute();
    static::assertSame(1, $permittedOrganizationResult->rowCount);
    static::assertSame('Foo', $permittedOrganizationResult->first()['title']);
    static::assertSame(['foo', 'bar'], $permittedOrganizationResult->first()['permissions']);
    static::assertTrue($permittedOrganizationResult->first()['PERM_foo']);
    static::assertTrue($permittedOrganizationResult->first()['PERM_bar']);

    // Contact has a relation that has a permitted type with a contact that has a permitted type
    $permittedIndividualResult = RemoteFundingProgram::get()
      ->setRemoteContactId((string) $this->permittedIndividualId)
      ->execute();
    static::assertSame(1, $permittedIndividualResult->rowCount);
    static::assertSame('Foo', $permittedIndividualResult->first()['title']);
    static::assertSame(['a', 'b'], $permittedIndividualResult->first()['permissions']);
    static::assertTrue($permittedIndividualResult->first()['PERM_a']);
    static::assertTrue($permittedIndividualResult->first()['PERM_b']);

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

  public function addFixtures(): void {
    $fundingProgramId = FundingProgram::create()
      ->setValues([
        'title' => 'Foo',
        'start_date' => '2022-10-22',
        'end_date' => '2023-10-22',
        'requests_start_date' => '2022-06-22',
        'requests_end_date' => '2022-12-31',
        'currency' => '€',
      ])->execute()->first()['id'];

    FundingProgram::create()
      ->setValues([
        'title' => 'Bar',
        'start_date' => '2022-10-22',
        'end_date' => '2023-10-22',
        'requests_start_date' => '2022-06-22',
        'requests_end_date' => '2022-12-31',
        'currency' => '€',
      ])->execute();

    $permittedContactTypeIdNoPermissions = ContactType::create()
      ->setValues([
        'name' => 'PermittedNoPermissions',
        'label' => 'permitted no permissions',
        'parent_id' => self::CONTACT_TYPE_ORGANIZATION_ID,
      ])->execute()->first()['id'];

    $permittedContactTypeId = ContactType::create()
      ->setValues([
        'name' => 'Permitted',
        'label' => 'permitted no permissions',
        'parent_id' => self::CONTACT_TYPE_ORGANIZATION_ID,
      ])->execute()->first()['id'];

    FundingProgramContactRelation::create()
      ->setValues([
        'funding_program_id' => $fundingProgramId,
        'entity_table' => 'civicrm_contact_type',
        'entity_id' => $permittedContactTypeId,
        'permissions' => ['foo', 'bar'],
      ])->execute();

    ContactType::create()
      ->setValues([
        'name' => 'NotPermitted',
        'label' => 'not permitted',
        'parent_id' => self::CONTACT_TYPE_ORGANIZATION_ID,
      ])->execute();

    $permittedRelationshipTypeId = RelationshipType::create()
      ->setValues([
        'name_a_b' => 'permitted',
        'name_b_a' => 'permitted',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Organization',
        'contact_sub_type_b' => 'PermittedNoPermissions',
      ])->execute()->first()['id'];

    $notPermittedRelationshipTypeId = RelationshipType::create()
      ->setValues([
        'name_a_b' => 'not permitted',
        'name_b_a' => 'not permitted',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Organization',
        'contact_sub_type_b' => 'PermittedNoPermissions',
      ])->execute()->first()['id'];

    $permittedContactRelationId = FundingProgramContactRelation::create()
      ->setValues([
        'funding_program_id' => $fundingProgramId,
        'entity_table' => 'civicrm_contact_type',
        'entity_id' => $permittedContactTypeIdNoPermissions,
      ])->execute()->first()['id'];

    FundingProgramContactRelation::create()
      ->setValues([
        'funding_program_id' => $fundingProgramId,
        'entity_table' => 'civicrm_relationship_type',
        'entity_id' => $permittedRelationshipTypeId,
        'parent_id' => $permittedContactRelationId,
        'permissions' => ['a', 'b'],
      ])->execute();

    $this->permittedOrganizationIdNoPermissions = Contact::create()->setValues([
      'contact_type' => 'Organization',
      'contact_sub_type' => 'PermittedNoPermissions',
      'legal_name' => 'Permitted Organization No Permissions',
    ])->execute()->first()['id'];

    $this->permittedOrganizationId = Contact::create()->setValues([
      'contact_type' => 'Organization',
      'contact_sub_type' => 'Permitted',
      'legal_name' => 'Permitted Organization',
    ])->execute()->first()['id'];

    $this->permittedIndividualId = Contact::create()
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Permitted',
        'last_name' => 'User',
      ])
      ->execute()->first()['id'];

    Relationship::create()
      ->setValues([
        'contact_id_a' => $this->permittedIndividualId,
        'contact_id_b' => $this->permittedOrganizationIdNoPermissions,
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
        'contact_id_b' => $this->permittedOrganizationIdNoPermissions,
        'relationship_type_id' => $notPermittedRelationshipTypeId,
      ])->execute();
  }

}
