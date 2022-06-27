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
 * @covers \Civi\Funding\EventSubscriber\RemoteFundingProgramPermissionsSubscriber
 */
final class RemoteFundingProgramTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  private const CONTACT_TYPE_ORGANIZATION_ID = 3;

  private int $permittedContactId;

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
    $permittedResult = RemoteFundingProgram::get()
      ->setRemoteContactId((string) $this->permittedContactId)
      ->execute();
    static::assertSame(1, $permittedResult->rowCount);
    static::assertSame('Foo', $permittedResult->first()['title']);

    $notPermittedResult = RemoteFundingProgram::get()
      ->setRemoteContactId((string) $this->notPermittedContactId)
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

    $permittedContactTypeId = ContactType::create()
      ->setValues([
        'name' => 'Permitted',
        'label' => 'permitted',
        'parent_id' => self::CONTACT_TYPE_ORGANIZATION_ID,
      ])->execute()->first()['id'];

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
        'contact_sub_type_b' => 'Permitted',
      ])->execute()->first()['id'];

    $notPermittedRelationshipTypeId = RelationshipType::create()
      ->setValues([
        'name_a_b' => 'not permitted',
        'name_b_a' => 'not permitted',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Organization',
        'contact_sub_type_b' => 'Permitted',
      ])->execute()->first()['id'];

    FundingProgramContactType::create()
      ->setValues([
        'funding_program_id' => $fundingProgramId,
        'contact_type_id' => $permittedContactTypeId,
        'relationship_type_id' => $permittedRelationshipTypeId,
      ])->execute();

    $permittedOrganizationId = Contact::create()
      ->setValues([
        'contact_type' => 'Organization',
        'contact_sub_type' => 'Permitted',
        'legal_name' => 'Permitted Organization',
      ])->execute()->first()['id'];

    $this->permittedContactId = Contact::create()
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Permitted',
        'last_name' => 'User',
      ])
      ->execute()->first()['id'];

    Relationship::create()
      ->setValues([
        'contact_id_a' => $this->permittedContactId,
        'contact_id_b' => $permittedOrganizationId,
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
        'contact_id_b' => $permittedOrganizationId,
        'relationship_type_id' => $notPermittedRelationshipTypeId,
      ])->execute();
  }

}
