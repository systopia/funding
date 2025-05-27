<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\Permission\ContactRelation\Loader;

use Civi\Api4\Relationship;
use Civi\Api4\RelationshipType;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\PHPUnit\Traits\ArrayAssertTrait;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\Permission\ContactRelation\Loader\ContactRelationshipsLoader
 *
 * @group headless
 */
final class ContactRelationshipsLoaderTest extends AbstractFundingHeadlessTestCase {

  use ArrayAssertTrait;

  private ContactRelationshipsLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $this->loader = new ContactRelationshipsLoader(Api4::getInstance());
  }

  public function testGetContacts(): void {
    $contact1 = ContactFixture::addIndividual();
    $contact2 = ContactFixture::addIndividual();
    // Related A to B
    $relatedContact1 = ContactFixture::addIndividual(['last_name' => 'Related 1']);
    // Related B to A
    $relatedContact2 = ContactFixture::addIndividual(['last_name' => 'Related 2']);
    // Wrong relationship type
    $notRelatedContact = ContactFixture::addIndividual(['last_name' => 'Not Related']);
    // Inactive relationship
    $inactiveRelationContact = ContactFixture::addIndividual(['last_name' => 'Inactive Relationship']);

    $relatedRelationshipTypeId1 = RelationshipType::create(FALSE)
      ->setValues([
        'name_a_b' => 'related1',
        'name_b_a' => 'related1',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    $relatedRelationshipTypeId2 = RelationshipType::create(FALSE)
      ->setValues([
        'name_a_b' => 'related2',
        'name_b_a' => 'related2',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    $notRelatedRelationshipTypeId = RelationshipType::create(FALSE)
      ->setValues([
        'name_b_a' => 'foo',
        'name_a_b' => 'bar',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact1['id'],
        'contact_id_b' => $relatedContact1['id'],
        'relationship_type_id' => $relatedRelationshipTypeId1,
      ])->execute();
    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact2['id'],
        'contact_id_b' => $relatedContact1['id'],
        'relationship_type_id' => $relatedRelationshipTypeId2,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $relatedContact2['id'],
        'contact_id_b' => $contact1['id'],
        'relationship_type_id' => $relatedRelationshipTypeId1,
      ])->execute();
    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $relatedContact2['id'],
        'contact_id_b' => $contact2['id'],
        'relationship_type_id' => $relatedRelationshipTypeId2,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact1['id'],
        'contact_id_b' => $notRelatedContact['id'],
        'relationship_type_id' => $notRelatedRelationshipTypeId,
      ])->execute();
    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact2['id'],
        'contact_id_b' => $notRelatedContact['id'],
        'relationship_type_id' => $relatedRelationshipTypeId2,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact1['id'],
        'contact_id_b' => $inactiveRelationContact['id'],
        'relationship_type_id' => $relatedRelationshipTypeId1,
        'is_active' => FALSE,
      ])->execute();
    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact2['id'],
        'contact_id_b' => $inactiveRelationContact['id'],
        'relationship_type_id' => $relatedRelationshipTypeId2,
      ])->execute();

    static::assertArrayHasSameKeys([
      $relatedContact1['id'],
      $relatedContact2['id'],
    ], $this->loader->getContacts('ContactRelationship', [
      'relationships' => [
        [
          'contactId' => $contact1['id'],
          'relationshipTypeId' => $relatedRelationshipTypeId1,
        ],
        [
          'contactId' => $contact2['id'],
          'relationshipTypeId' => $relatedRelationshipTypeId2,
        ],
      ],
    ]));
  }

  public function testSupportsRelationType(): void {
    static::assertTrue($this->loader->supportsRelationType('ContactRelationships'));
    static::assertFalse($this->loader->supportsRelationType('ContactRelationshipsX'));
  }

}
