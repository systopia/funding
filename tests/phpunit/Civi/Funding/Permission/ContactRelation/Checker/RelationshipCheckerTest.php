<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Permission\ContactRelation\Checker;

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
 * @covers \Civi\Funding\Permission\ContactRelation\Checker\RelationshipChecker
 *
 * @group headless
 */
final class RelationshipCheckerTest extends AbstractFundingHeadlessTestCase {

  use ArrayAssertTrait;

  private RelationshipChecker $checker;

  protected function setUp(): void {
    parent::setUp();
    $this->checker = new RelationshipChecker(Api4::getInstance());
  }

  public function testHasRelation(): void {
    $contact = ContactFixture::addIndividual();

    $contactType1 = ContactTypeFixture::addIndividualFixture('testType1');
    $contactType2 = ContactTypeFixture::addIndividualFixture('testType2');

    $group1 = GroupFixture::addFixture();
    $group2 = GroupFixture::addFixture();
    $inactiveGroup = GroupFixture::addFixture(['is_active' => FALSE]);

    // Relationship type 1 (A to B) with sub type1 in group 1.
    $relatedContact1 = ContactFixture::addIndividual(
      ['contact_sub_type' => 'testType1'],
    );
    // Relationship type 2 (B to A) with sub type2 in inactive group.
    GroupContactFixture::addFixtureWithGroupId($group1['id'], $relatedContact1['id']);
    $relatedContact2 = ContactFixture::addIndividual(
      ['contact_sub_type' => 'testType2'],
    );
    GroupContactFixture::addFixtureWithGroupId($inactiveGroup['id'], $relatedContact2['id']);

    // Inactive relationship.
    $inactiveRelationContact = ContactFixture::addIndividual(['last_name' => 'Inactive Relationship']);

    $notRelatedContact = ContactFixture::addIndividual();

    $relationshipType1Id = RelationshipType::create(FALSE)
      ->setValues([
        'name_a_b' => 'foo1',
        'name_b_a' => 'bar1',
        'contact_type_a' => 'Individual',
        'contact_type_b' => 'Individual',
      ])->execute()->first()['id'];

    $relationshipType2Id = RelationshipType::create(FALSE)
      ->setValues([
        'name_b_a' => 'foo2',
        'name_a_b' => 'bar2',
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

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact['id'],
        'contact_id_b' => $relatedContact1['id'],
        'relationship_type_id' => $relationshipType1Id,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $relatedContact2['id'],
        'contact_id_b' => $contact['id'],
        'relationship_type_id' => $relationshipType2Id,
      ])->execute();

    Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact['id'],
        'contact_id_b' => $inactiveRelationContact['id'],
        'relationship_type_id' => $inactiveRelationshipTypeId,
        'is_active' => FALSE,
      ])->execute();

    // Match relationship type, contact type, and group. (A to B)
    static::assertTrue($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [$group1['id']],
    ]));

    // Match relationship type, contact type. (B to A)
    static::assertTrue($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType2Id],
      'contactTypeIds' => [$contactType2['id']],
      'groupIds' => [],
    ]));

    // Match relationship type.
    static::assertTrue($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [],
      'groupIds' => [],
    ]));

    // Match contact type.
    static::assertTrue($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [],
    ]));

    // Match group.
    static::assertTrue($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [$group1['id']],
    ]));

    // Relationship type differs.
    static::assertFalse($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType2Id],
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [$group1['id']],
    ]));

    // Contact type differs.
    static::assertFalse($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [$contactType2['id']],
      'groupIds' => [$group1['id']],
    ]));

    // Group differs.
    static::assertFalse($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$relationshipType1Id],
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [$group2['id']],
    ]));

    // Inactive relationship.
    static::assertFalse($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [$inactiveRelationshipTypeId],
      'contactTypeIds' => [],
      'groupIds' => [],
    ]));

    // Inactive group.
    static::assertFalse($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [$inactiveGroup['id']],
    ]));

    // Any relationship.
    static::assertTrue($this->checker->hasRelation($contact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [],
    ]));

    // Not related contact has no relationship.
    static::assertFalse($this->checker->hasRelation($notRelatedContact['id'], 'Relationship', [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [],
    ]));
  }

  public function testSupportsRelationType(): void {
    static::assertTrue($this->checker->supportsRelationType('Relationship'));
    static::assertFalse($this->checker->supportsRelationType('RelationshipX'));
  }

}
