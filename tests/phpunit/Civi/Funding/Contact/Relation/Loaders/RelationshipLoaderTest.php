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

namespace Civi\Funding\Contact\Relation\Loaders;

use Civi\Api4\Relationship;
use Civi\Api4\RelationshipType;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\Funding\Fixtures\GroupContactFixture;
use Civi\Funding\Fixtures\GroupFixture;
use Civi\PHPUnit\Traits\ArrayAssertTrait;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\Contact\Relation\Loaders\RelationshipLoader
 *
 * @group headless
 */
final class RelationshipLoaderTest extends AbstractFundingHeadlessTestCase {

  use ArrayAssertTrait;

  private RelationshipLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $this->loader = new RelationshipLoader(Api4::getInstance());
  }

  public function testGetRelatedContacts(): void {
    $contact = ContactFixture::addIndividual();

    $contactType1 = ContactTypeFixture::addIndividualFixture('testType1');
    $contactType2 = ContactTypeFixture::addIndividualFixture('testType2');
    ContactTypeFixture::addIndividualFixture('testType3');

    $group1 = GroupFixture::addFixture();
    $group2 = GroupFixture::addFixture();
    $group3Inactive = GroupFixture::addFixture(['is_active' => FALSE]);

    $contactInGroup1 = ContactFixture::addIndividual();
    GroupContactFixture::addFixtureWithGroupId($group1['id'], $contactInGroup1['id']);
    $contactWithSubType1And3InGroup1 = ContactFixture::addIndividual([
      'contact_sub_type' => ['testType1', 'testType3'],
      'last_name' => 'With Sub Type 1 and 3',
    ]);
    GroupContactFixture::addFixtureWithGroupId($group1['id'], $contactWithSubType1And3InGroup1['id']);
    $contactWithSubType2And3InGroup3 = ContactFixture::addIndividual([
      'contact_sub_type' => ['testType2', 'testType3'],
      'last_name' => 'With Sub Type 2 and 3',
    ]);
    GroupContactFixture::addFixtureWithGroupId($group3Inactive['id'], $contactWithSubType2And3InGroup3['id']);

    // Inactive relationship
    $inactiveRelationContact = ContactFixture::addIndividual(['last_name' => 'Inactive Relationship']);

    $relationshipType1Id = RelationshipType::create(FALSE)
      ->setValues([
        'name_a_b' => 'foo1',
        'name_b_a' => 'bar1',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    $inactiveRelationshipTypeId = RelationshipType::create(FALSE)
      ->setValues([
        'name_a_b' => 'inactive',
        'name_b_a' => 'inactive',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
        'is_active' => FALSE,
      ])->execute()->first()['id'];

    $relationshipType2Id = RelationshipType::create(FALSE)
      ->setValues([
        'name_b_a' => 'foo2',
        'name_a_b' => 'bar2',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contactWithSubType1And3InGroup1['id'],
        'contact_id_b' => $contact['id'],
        'relationship_type_id' => $relationshipType1Id,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contactInGroup1['id'],
        'contact_id_b' => $contact['id'],
        'relationship_type_id' => $relationshipType1Id,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact['id'],
        'contact_id_b' => $contactWithSubType2And3InGroup3['id'],
        'relationship_type_id' => $relationshipType1Id,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact['id'],
        'contact_id_b' => $contactInGroup1['id'],
        'relationship_type_id' => $relationshipType2Id,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contactInGroup1['id'],
        'contact_id_b' => $inactiveRelationContact['id'],
        'relationship_type_id' => $inactiveRelationshipTypeId,
        'is_active' => FALSE,
      ])->execute();

    // Match relationship type, contact type, and group.
    static::assertArrayHasSameKeys([
      $contactWithSubType1And3InGroup1['id'],
    ], $this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [$group1['id']],
    ]));

    // Match relationship type.
    static::assertArrayHasSameKeys([
      $contactWithSubType1And3InGroup1['id'],
      $contactInGroup1['id'],
      $contactWithSubType2And3InGroup3['id'],
    ], $this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [],
      'groupIds' => [],
    ]));

    // Match group.
    static::assertArrayHasSameKeys([
      $contactWithSubType1And3InGroup1['id'],
      $contactInGroup1['id'],
    ], $this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [$group1['id']],
    ]));

    // Match contact type.
    static::assertArrayHasSameKeys([
      $contactWithSubType1And3InGroup1['id'],
    ], $this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [],
    ]));

    // Match relationship type and contact type.
    static::assertArrayHasSameKeys([
      $contactWithSubType2And3InGroup3['id'],
    ], $this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [$contactType2['id']],
      'groupIds' => [],
    ]));

    // Relationship type differs.
    static::assertEmpty($this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType2Id],
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [$group1['id']],
    ]));

    // Contact type differs.
    static::assertEmpty($this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [$contactType2['id']],
      'groupIds' => [$group1['id']],
    ]));

    // Group differs.
    static::assertEmpty($this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [$group2['id']],
    ]));

    // Inactive group has no result.
    static::assertEmpty($this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [$group3Inactive['id']],
    ]));

    // Inactive relationship has no result.
    static::assertEmpty($this->loader->getRelatedContacts($inactiveRelationContact['id'], 'Relationship', [
      'relationshipTypeIds' => [$inactiveRelationshipTypeId],
      'contactTypeIds' => [],
      'groupIds' => [],
    ]));

    // Match all related contacts with active relationship.
    static::assertCount(3, $this->loader->getRelatedContacts($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [],
    ]));
  }

  public function testSupportsRelationType(): void {
    static::assertTrue($this->loader->supportsRelationType('Relationship'));
    static::assertFalse($this->loader->supportsRelationType('RelationshipX'));
  }

}
