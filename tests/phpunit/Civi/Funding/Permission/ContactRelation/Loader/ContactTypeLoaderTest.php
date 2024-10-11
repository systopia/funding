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

use Civi\Api4\Contact;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\PHPUnit\Traits\ArrayAssertTrait;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\Permission\ContactRelation\Loader\ContactTypeLoader
 *
 * @group headless
 */
final class ContactTypeLoaderTest extends AbstractFundingHeadlessTestCase {

  use ArrayAssertTrait;

  private ContactTypeLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $this->loader = new ContactTypeLoader(Api4::getInstance());
  }

  public function testGetContacts(): void {
    Contact::delete(FALSE)
      ->addWhere('id', '>', 0)
      ->execute();

    $contactType = ContactTypeFixture::addIndividualFixture('testType');
    ContactTypeFixture::addIndividualFixture('testType2');
    $contact = ContactFixture::addIndividual();
    $contactWithSubType = ContactFixture::addIndividual(['contact_sub_type' => ['testType', 'testType2']]);

    static::assertArrayHasSameKeys(
      [$contactWithSubType['id']],
      $this->loader->getContacts('ContactType', [
        'contactTypeId' => $contactType['id'],
      ]));

    static::assertArrayHasSameKeys([
      $contact['id'],
      $contactWithSubType['id'],
    ], $this->loader->getContacts('ContactType', [
      'contactTypeId' => ContactTypeFixture::CONTACT_TYPE_INDIVIDUAL_ID,
    ]));
  }

  public function testSupportsRelationType(): void {
    static::assertTrue($this->loader->supportsRelationType('ContactType'));
    static::assertFalse($this->loader->supportsRelationType('ContactTypeX'));
  }

}
