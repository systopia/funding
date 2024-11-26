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

namespace Civi\Api4\Traits;

use Civi\Api4\Contact;
use Civi\Api4\Relationship;
use Civi\Api4\RelationshipType;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;

trait FundingProgramTestFixturesTrait {

  /**
   * @throws \CRM_Core_Exception
   */
  protected function addFixtures(): void {
    $this->doAddFixtures(
      ['application_foo', 'review_bar'],
      ['application_a', 'review_b']
    );
  }

  /**
   * @phpstan-param list<string> $permittedContactTypePermissions
   * @phpstan-param list<string> $permittedRelationshipTypePermissions
   *
   * @throws \CRM_Core_Exception
   */
  private function doAddFixtures(
    array $permittedContactTypePermissions,
    array $permittedRelationshipTypePermissions
  ): void {
    $fundingProgramId = FundingProgramFixture::addFixture(['title' => 'Foo', 'abbreviation' => 'FOO'])->getId();
    FundingProgramFixture::addFixture(['title' => 'Bar', 'abbreviation' => 'BAR']);

    $permittedContactTypeIdNoPermissions = ContactTypeFixture::addOrganizationFixture(
      'PermittedNoPermissions', 'permitted no permissions')['id'];

    $permittedContactTypeId = ContactTypeFixture::addOrganizationFixture(
      'Permitted', 'permitted')['id'];

    FundingProgramContactRelationFixture::addFixture(
      $fundingProgramId,
      'ContactTypeAndGroup',
      ['contactTypeIds' => [$permittedContactTypeId]],
      $permittedContactTypePermissions
    );

    ContactTypeFixture::addOrganizationFixture('NotPermitted', 'not permitted');

    $permittedRelationshipTypeId = RelationshipType::create(FALSE)
      ->setValues([
        'name_a_b' => 'permitted',
        'name_b_a' => 'permitted',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Organization',
        'contact_sub_type_b' => 'PermittedNoPermissions',
      ])->execute()->first()['id'];

    $notPermittedRelationshipTypeId = RelationshipType::create(FALSE)
      ->setValues([
        'name_a_b' => 'not permitted',
        'name_b_a' => 'not permitted',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Organization',
        'contact_sub_type_b' => 'PermittedNoPermissions',
      ])->execute()->first()['id'];

    FundingProgramContactRelationFixture::addFixture(
      $fundingProgramId,
      'Relationship',
      [
        'contactTypeIds' => [$permittedContactTypeIdNoPermissions],
        'relationshipTypeIds' => [$permittedRelationshipTypeId],
        'groupIds' => [],
      ],
      $permittedRelationshipTypePermissions
    );

    $this->permittedOrganizationIdNoPermissions = Contact::create(FALSE)
      ->setValues([
        'contact_type' => 'Organization',
        'contact_sub_type' => 'PermittedNoPermissions',
        'legal_name' => 'Permitted Organization No Permissions',
      ])->execute()->first()['id'];

    $this->permittedOrganizationId = Contact::create(FALSE)
      ->setValues([
        'contact_type' => 'Organization',
        'contact_sub_type' => 'Permitted',
        'legal_name' => 'Permitted Organization',
      ])->execute()->first()['id'];

    $this->permittedIndividualId = Contact::create(FALSE)
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'Permitted',
        'last_name' => 'User',
      ])
      ->execute()->first()['id'];

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $this->permittedIndividualId,
        'contact_id_b' => $this->permittedOrganizationIdNoPermissions,
        'relationship_type_id' => $permittedRelationshipTypeId,
      ])->execute();

    $this->notPermittedContactId = Contact::create(FALSE)
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'NotPermitted',
        'last_name' => 'User',
      ])
      ->execute()->first()['id'];

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $this->notPermittedContactId,
        'contact_id_b' => $this->permittedOrganizationIdNoPermissions,
        'relationship_type_id' => $notPermittedRelationshipTypeId,
      ])->execute();
  }

}
