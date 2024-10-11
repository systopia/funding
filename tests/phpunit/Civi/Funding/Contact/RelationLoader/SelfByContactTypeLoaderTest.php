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

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Contact\Relation\Loaders\SelfByContactTypeLoader;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\Contact\Relation\Loaders\SelfByContactTypeLoader
 *
 * @group headless
 */
final class SelfByContactTypeLoaderTest extends AbstractFundingHeadlessTestCase {

  private SelfByContactTypeLoader $relatedContactLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->relatedContactLoader = new SelfByContactTypeLoader(new Api4());
  }

  public function testGetRelatedContacts(): void {
    $contactType = ContactTypeFixture::addFixture(['name' => 'testType']);
    $contactSubType = ContactTypeFixture::addIndividualFixture('testSubType');
    ContactTypeFixture::addIndividualFixture('testSubType2');
    $contact1 = ContactFixture::addIndividual(['nick_name' => 'Contact 1', 'contact_type' => 'testType']);
    $contact2 = ContactFixture::addIndividual([
      'nick_name' => 'Contact 2',
      'contact_sub_type' => ['testSubType', 'testSubType2'],
    ]);

    $relatedContacts1 = $this->relatedContactLoader->getRelatedContacts(
      $contact1['id'],
      'SelfByContactType',
      ['contactTypeId' => $contactType['id']]
    );
    static::assertSame([$contact1['id']], array_keys($relatedContacts1));
    static::assertSame('Contact 1', $relatedContacts1[$contact1['id']]['nick_name']);
    static::assertSame([], $this->relatedContactLoader->getRelatedContacts(
      $contact2['id'],
      'SelfByContactType',
      ['contactTypeId' => $contactType['id']])
    );

    $relatedContacts2 = $this->relatedContactLoader->getRelatedContacts(
      $contact2['id'],
      'SelfByContactType',
      ['contactTypeId' => $contactSubType['id']]
    );
    static::assertSame([$contact2['id']], array_keys($relatedContacts2));
    static::assertSame('Contact 2', $relatedContacts2[$contact2['id']]['nick_name']);

    static::assertSame([], $this->relatedContactLoader->getRelatedContacts(
      $contact1['id'],
      'SelfByContactType',
      ['contactTypeId' => $contactSubType['id']]
    ));
  }

  public function testSupportsRelation(): void {
    static::assertTrue($this->relatedContactLoader->supportsRelationType('SelfByContactType'));
    static::assertFalse($this->relatedContactLoader->supportsRelationType('Test'));
  }

}
