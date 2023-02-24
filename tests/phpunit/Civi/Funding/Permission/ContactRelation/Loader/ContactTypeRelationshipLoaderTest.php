<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\PHPUnit\Traits\ArrayAssertTrait;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\Permission\ContactRelation\Loader\ContactTypeRelationshipLoader
 *
 * @group headless
 */
final class ContactTypeRelationshipLoaderTest extends AbstractFundingHeadlessTestCase {

  use ArrayAssertTrait;

  private ContactTypeRelationshipLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $this->loader = new ContactTypeRelationshipLoader(Api4::getInstance());
  }

  public function testGetContacts(): void {
    $contactType1 = ContactTypeFixture::addIndividualFixture('testType1');
    $contactType2 = ContactTypeFixture::addIndividualFixture('testType2');

    $contact = ContactFixture::addIndividual();
    $contactWithSubType1 = ContactFixture::addIndividual([
      'contact_sub_type' => 'testType1',
      'last_name' => 'With Sub Type1',
    ]);
    $contactWithSubType2 = ContactFixture::addIndividual([
      'contact_sub_type' => 'testType2',
      'last_name' => 'With Sub Type2',
    ]);

    // Related A to B with sub type1
    $relatedContact1 = ContactFixture::addIndividual(['last_name' => 'Related 1']);
    // Related A to B without sub type
    $relatedContact2 = ContactFixture::addIndividual(['last_name' => 'Related 2']);
    // Related B to A with sub type2
    $relatedContact3 = ContactFixture::addIndividual(['last_name' => 'Related 1']);
    // Related B to A without sub type
    $relatedContact4 = ContactFixture::addIndividual(['last_name' => 'Related 2']);

    // Wrong relationship type
    $notRelatedContact = ContactFixture::addIndividual(['last_name' => 'Not Related']);

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
        'contact_id_a' => $contactWithSubType1['id'],
        'contact_id_b' => $relatedContact1['id'],
        'relationship_type_id' => $relatedRelationshipTypeId,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact['id'],
        'contact_id_b' => $relatedContact2['id'],
        'relationship_type_id' => $relatedRelationshipTypeId,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $relatedContact3['id'],
        'contact_id_b' => $contactWithSubType2['id'],
        'relationship_type_id' => $relatedRelationshipTypeId,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $relatedContact3['id'],
        'contact_id_b' => $contact['id'],
        'relationship_type_id' => $relatedRelationshipTypeId,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contactWithSubType1['id'],
        'contact_id_b' => $notRelatedContact['id'],
        'relationship_type_id' => $notRelatedRelationshipTypeId,
      ])->execute();

    static::assertArrayHasSameKeys([
      $relatedContact1['id'],
    ], $this->loader->getContacts('ContactTypeRelationship', [
      'relationshipTypeId' => $relatedRelationshipTypeId,
      'contactTypeId' => $contactType1['id'],
    ]));

    static::assertArrayHasSameKeys([
      $relatedContact3['id'],
    ], $this->loader->getContacts('ContactTypeRelationship', [
      'relationshipTypeId' => $relatedRelationshipTypeId,
      'contactTypeId' => $contactType2['id'],
    ]));
  }

  public function testSupportsRelationType(): void {
    static::assertTrue($this->loader->supportsRelationType('ContactTypeRelationship'));
    static::assertFalse($this->loader->supportsRelationType('ContactTypeRelationshipX'));
  }

}
