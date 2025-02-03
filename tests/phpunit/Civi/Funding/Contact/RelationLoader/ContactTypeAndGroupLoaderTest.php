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

namespace Civi\Funding\Contact\RelationLoader;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Contact\Relation\Loaders\ContactTypeAndGroupLoader;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\Funding\Fixtures\GroupContactFixture;
use Civi\Funding\Fixtures\GroupFixture;
use Civi\PHPUnit\Traits\ArrayAssertTrait;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\Permission\ContactRelation\Loader\RelationshipLoader
 *
 * @group headless
 */
final class ContactTypeAndGroupLoaderTest extends AbstractFundingHeadlessTestCase {

  use ArrayAssertTrait;

  private ContactTypeAndGroupLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $this->loader = new ContactTypeAndGroupLoader(Api4::getInstance());
  }

  public function testGetRelatedContacts(): void {
    $contactType1 = ContactTypeFixture::addIndividualFixture('testType1');
    $contactType2 = ContactTypeFixture::addIndividualFixture('testType2');
    ContactTypeFixture::addIndividualFixture('testType3');

    $group1 = GroupFixture::addFixture();
    $group2 = GroupFixture::addFixture();
    $group3Inactive = GroupFixture::addFixture(['is_active' => FALSE]);

    $contactWithSubType1And3InGroup1 = ContactFixture::addIndividual([
      'contact_sub_type' => ['testType1', 'testType3'],
      'last_name' => 'With Sub Type1',
    ]);
    GroupContactFixture::addFixtureWithGroupId($group1['id'], $contactWithSubType1And3InGroup1['id']);
    $contactWithSubType2InGroup3 = ContactFixture::addIndividual([
      'contact_sub_type' => 'testType2',
      'last_name' => 'With Sub Type2',
    ]);
    GroupContactFixture::addFixtureWithGroupId($group3Inactive['id'], $contactWithSubType2InGroup3['id']);

    // Match contact type, and group.
    static::assertArrayHasSameKeys([
      $contactWithSubType1And3InGroup1['id'],
    ], $this->loader->getRelatedContacts(1234, 'ContactTypeAndGroup', [
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [$group1['id']],
    ]));

    // Match group.
    static::assertArrayHasSameKeys([
      $contactWithSubType1And3InGroup1['id'],
    ], $this->loader->getRelatedContacts(1234, 'ContactTypeAndGroup', [
      'contactTypeIds' => [],
      'groupIds' => [$group1['id']],
    ]));

    // Match contact type.
    static::assertArrayHasSameKeys([
      $contactWithSubType1And3InGroup1['id'],
    ], $this->loader->getRelatedContacts(1234, 'ContactTypeAndGroup', [
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [],
    ]));

    // Contact type differs.
    static::assertEmpty($this->loader->getRelatedContacts(1234, 'ContactTypeAndGroup', [
      'contactTypeIds' => [$contactType2['id']],
      'groupIds' => [$group1['id']],
    ]));

    // Group differs.
    static::assertEmpty($this->loader->getRelatedContacts(1234, 'ContactTypeAndGroup', [
      'contactTypeIds' => [$contactType1['id']],
      'groupIds' => [$group2['id']],
    ]));

    // Inactive group has no result.
    static::assertEmpty($this->loader->getRelatedContacts(1234, 'ContactTypeAndGroup', [
      'contactTypeIds' => [],
      'groupIds' => [$group3Inactive['id']],
    ]));
  }

  public function testSupportsRelationType(): void {
    static::assertTrue($this->loader->supportsRelationType('ContactTypeAndGroup'));
    static::assertFalse($this->loader->supportsRelationType('ContactTypeAndGroupX'));
  }

}
