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

namespace Civi\Funding\Contact\RelationLoader;

use Civi\Api4\Relationship;
use Civi\Api4\RelationshipType;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Contact\Relation\Loaders\ContactTypeAndRelationshipTypeLoader;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\Contact\Relation\Loaders\ContactTypeAndRelationshipTypeLoader
 *
 * @group headless
 */
final class ContactTypeAndRelationshipTypeLoaderTest extends AbstractFundingHeadlessTestCase {

  private ContactTypeAndRelationshipTypeLoader $relatedContactLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->relatedContactLoader = new ContactTypeAndRelationshipTypeLoader(new Api4());
  }

  public function testGetRelatedContacts(): void {
    $contactTypeId = ContactTypeFixture::addIndividualFixture('testSubType')['id'];
    ContactTypeFixture::addIndividualFixture('testSubType2');
    $contact = ContactFixture::addIndividual();
    // Related A to B
    $relatedContact1 = ContactFixture::addIndividual(['last_name' => 'Related 1', 'contact_sub_type' => 'testSubType']);
    // Related B to A
    $relatedContact2 = ContactFixture::addIndividual(['last_name' => 'Related 2', 'contact_sub_type' => 'testSubType']);
    // Wrong relationship type
    $notRelatedContact1 = ContactFixture::addIndividual([
      'last_name' => 'Not Related 1',
      'contact_sub_type' => ['testSubType', 'testSubType2'],
    ]);
    // Inactive relationship
    $inactiveRelationContact = ContactFixture::addIndividual([
      'last_name' => 'Inactive Relationship',
      'contact_sub_type' => ['testSubType', 'testSubType2'],
    ]);
    // Wrong contact type
    $notRelatedContact2 = ContactFixture::addIndividual(['last_name' => 'Not Related 2']);

    $relatedRelationshipTypeId = RelationshipType::create(FALSE)
      ->setValues([
        'name_a_b' => 'related',
        'name_b_a' => 'related',
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
        'contact_id_a' => $contact['id'],
        'contact_id_b' => $relatedContact1['id'],
        'relationship_type_id' => $relatedRelationshipTypeId,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $relatedContact2['id'],
        'contact_id_b' => $contact['id'],
        'relationship_type_id' => $relatedRelationshipTypeId,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact['id'],
        'contact_id_b' => $notRelatedContact1['id'],
        'relationship_type_id' => $notRelatedRelationshipTypeId,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $notRelatedContact2['id'],
        'contact_id_b' => $contact['id'],
        'relationship_type_id' => $relatedRelationshipTypeId,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact['id'],
        'contact_id_b' => $inactiveRelationContact['id'],
        'relationship_type_id' => $relatedRelationshipTypeId,
        'is_active' => FALSE,
      ])->execute();

    $relatedContacts = $this->relatedContactLoader->getRelatedContacts(
      $contact['id'],
      'ContactTypeAndRelationshipType',
      [
        'contactTypeId' => $contactTypeId,
        'relationshipTypeId' => $relatedRelationshipTypeId,
      ]
    );
    static::assertEquals([$relatedContact1['id'], $relatedContact2['id']], array_keys($relatedContacts));
    static::assertSame('Related 1', $relatedContacts[$relatedContact1['id']]['last_name']);
    static::assertSame('Related 2', $relatedContacts[$relatedContact2['id']]['last_name']);
  }

  public function testSupportsRelation(): void {
    static::assertTrue($this->relatedContactLoader->supportsRelationType('ContactTypeAndRelationshipType'));
    static::assertFalse($this->relatedContactLoader->supportsRelationType('Test'));
  }

}
