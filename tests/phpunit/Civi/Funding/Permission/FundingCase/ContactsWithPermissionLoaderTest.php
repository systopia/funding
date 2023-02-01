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

namespace Civi\Funding\Permission\FundingCase;

use Civi\Funding\Entity\FundingCaseContactRelationEntity;
use Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Permission\FundingCase\ContactsWithPermissionLoader
 */
final class ContactsWithPermissionLoaderTest extends TestCase {

  /**
   * @var \Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $contactRelationLoaderMock;

  private ContactsWithPermissionLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $this->contactRelationLoaderMock = $this->createMock(ContactRelationLoaderInterface::class);
    $this->loader = new ContactsWithPermissionLoader($this->contactRelationLoaderMock);
  }

  public function testGetContactsWithPermission(): void {
    $contactRelations = [
      FundingCaseContactRelationEntity::fromArray([
        'funding_case_id' => 2,
        'type' => 'rel1',
        'properties' => ['key1' => 'val1'],
        'permissions' => ['foo', 'bar'],
      ]),
      FundingCaseContactRelationEntity::fromArray([
        'funding_case_id' => 2,
        'type' => 'rel2',
        'properties' => ['key2' => 'val2'],
        'permissions' => ['foo', 'bar'],
      ]),
      FundingCaseContactRelationEntity::fromArray([
        'funding_case_id' => 2,
        'type' => 'rel3',
        'properties' => ['key3' => 'val3'],
        'permissions' => ['bar'],
      ]),
    ];

    $this->contactRelationLoaderMock->expects(static::exactly(2))->method('getContacts')
      ->withConsecutive(
        ['rel1', ['key1' => 'val1']],
        ['rel2', ['key2' => 'val2']],
      )->willReturnOnConsecutiveCalls(
        [
          1 => ['id' => 1, 'display_name' => 'Contact1'],
          2 => ['id' => 2, 'display_name' => 'Contact2_1'],
        ],
        [
          3 => ['id' => 3, 'display_name' => 'Contact3'],
          2 => ['id' => 2, 'display_name' => 'Contact2_2'],
        ],
      );

    $expectedContacts = [
      1 => ['id' => 1, 'display_name' => 'Contact1'],
      2 => ['id' => 2, 'display_name' => 'Contact2_1'],
      3 => ['id' => 3, 'display_name' => 'Contact3'],
    ];
    static::assertEquals($expectedContacts, $this->loader->getContactsWithPermission($contactRelations, 'foo'));
  }

}
