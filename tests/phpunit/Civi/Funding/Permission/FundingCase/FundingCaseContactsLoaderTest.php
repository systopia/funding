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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\Generic\BasicGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Entity\FundingCaseContactRelationEntity;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Permission\FundingCase\FundingCaseContactsLoader
 */
final class FundingCaseContactsLoaderTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\Permission\FundingCase\FundingCaseContactsLoader
   */
  private FundingCaseContactsLoader $contactsLoader;

  /**
   * @var \Civi\Funding\Permission\FundingCase\ContactsWithPermissionLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $contactsWithPermissionLoaderMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->contactsWithPermissionLoaderMock = $this->createMock(ContactsWithPermissionLoader::class);
    $this->contactsLoader = new FundingCaseContactsLoader(
      $this->api4Mock,
      $this->contactsWithPermissionLoaderMock,
    );
  }

  public function testGetContactsWithPermission(): void {
    $actionMock = new BasicGetAction(FundingCaseContactRelation::_getEntityName(), 'get');
    $this->api4Mock->method('createGetAction')
      ->with(FundingCaseContactRelation::_getEntityName())
      ->willReturn($actionMock);

    $contactRelations = [
      FundingCaseContactRelationEntity::fromArray([
        'funding_case_id' => FundingCaseFactory::DEFAULT_ID,
        'type' => 'rel1',
        'properties' => ['key1' => 'val1'],
        'permissions' => ['foo'],
      ]),
      FundingCaseContactRelationEntity::fromArray([
        'funding_case_id' => FundingCaseFactory::DEFAULT_ID,
        'type' => 'rel2',
        'properties' => ['key2' => 'val2'],
        'permissions' => ['bar'],
      ]),
    ];

    $result = new Result(array_map(
      fn (FundingCaseContactRelationEntity $relation) => $relation->toArray(),
      $contactRelations
    ));
    $this->api4Mock->method('executeAction')->with(static::callback(function (BasicGetAction $action) {
      static::assertSame([['funding_case_id', '=', FundingCaseFactory::DEFAULT_ID]], $action->getWhere());

      return TRUE;
    }))->willReturn($result);

    $contacts = [
      1 => ['id' => 1, 'name' => 'Contact1'],
      2 => ['id' => 2, 'name' => 'Contact2'],
    ];
    $this->contactsWithPermissionLoaderMock->method('getContactsWithPermission')
      ->with($contactRelations, 'test')
      ->willReturn($contacts);

    $fundingCase = FundingCaseFactory::createFundingCase();
    static::assertSame($contacts, $this->contactsLoader->getContactsWithPermission($fundingCase, 'test'));
  }

}
