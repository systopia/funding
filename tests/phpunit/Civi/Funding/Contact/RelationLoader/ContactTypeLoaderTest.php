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

use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\RemoteTools\Api4\Api4;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Contact\RelationLoader\ContactTypeLoader
 *
 * @group headless
 */
final class ContactTypeLoaderTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  private ContactTypeLoader $relatedContactLoader;

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  protected function setUp(): void {
    parent::setUp();
    $this->relatedContactLoader = new ContactTypeLoader(new Api4());
  }

  public function testGetRelatedContacts(): void {
    $contactType = ContactTypeFixture::addFixture(['name' => 'testType']);
    $contactSubType = ContactTypeFixture::addIndividualFixture('testSubType');
    $contact1 = ContactFixture::addIndividual(['nick_name' => 'Contact 1', 'contact_type' => 'testType']);
    $contact2 = ContactFixture::addIndividual(['nick_name' => 'Contact 2', 'contact_sub_type' => 'testSubType']);

    $contactRelation1 = [
      'id' => 1,
      'entity_table' => 'civicrm_contact_type',
      'entity_id' => $contactType['id'],
      'parent_id' => NULL,
    ];
    $relatedContacts1 = $this->relatedContactLoader->getRelatedContacts($contact1['id'], $contactRelation1, NULL);
    static::assertSame([$contact1['id']], array_keys($relatedContacts1));
    static::assertSame('Contact 1', $relatedContacts1[$contact1['id']]['nick_name']);
    static::assertSame([], $this->relatedContactLoader->getRelatedContacts($contact2['id'], $contactRelation1, NULL));

    $contactRelation2 = [
      'id' => 2,
      'entity_table' => 'civicrm_contact_type',
      'entity_id' => $contactSubType['id'],
      'parent_id' => NULL,
    ];
    $relatedContacts2 = $this->relatedContactLoader->getRelatedContacts($contact2['id'], $contactRelation2, NULL);
    static::assertSame([$contact2['id']], array_keys($relatedContacts2));
    static::assertSame('Contact 2', $relatedContacts2[$contact2['id']]['nick_name']);
    static::assertSame([], $this->relatedContactLoader->getRelatedContacts($contact1['id'], $contactRelation2, NULL));
  }

  public function testSupportsRelation(): void {

    $contactRelation1 = [
      'id' => 1,
      'entity_table' => 'civicrm_contact_type',
      'entity_id' => 1,
      'parent_id' => NULL,
    ];
    static::assertTrue($this->relatedContactLoader->supportsRelation($contactRelation1, NULL));

    $contactRelation2 = [
      'id' => 2,
      'entity_table' => 'civicrm_foo',
      'entity_id' => 1,
      'parent_id' => NULL,
    ];
    static::assertFalse($this->relatedContactLoader->supportsRelation($contactRelation2, NULL));

    $contactRelation3 = [
      'id' => 3,
      'entity_table' => 'civicrm_contact_type',
      'entity_id' => 1,
      'parent_id' => 1,
    ];
    static::assertFalse($this->relatedContactLoader->supportsRelation($contactRelation3, $contactRelation1));
  }

}
