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

use Civi\Api4\Generic\BasicGetAction;
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Permission\ContactRelation\Loader\ContactLoader
 */
final class ContactLoaderTest extends TestCase {

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  private ContactLoader $loader;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->loader = new ContactLoader($this->api4Mock);
  }

  public function testGetContacts(): void {
    $action = new BasicGetAction('Contact', 'get');
    $this->api4Mock->method('createGetAction')->with('Contact')->willReturn($action);
    $result = new Result([
      ['id' => 2, 'name' => 'test2'],
    ]);
    $this->api4Mock->method('executeAction')->with(static::callback(function ($arg) use ($action) {
      static::assertSame($action, $arg);
      static::assertSame([['id', '=', 2]], $action->getWhere());

      return TRUE;
    }))->willReturn($result);

    $contacts = $this->loader->getContacts('Contact', ['contactId' => 2]);
    static::assertSame([2 => ['id' => 2, 'name' => 'test2']], $contacts);
  }

  public function testSupportsRelationType(): void {
    static::assertTrue($this->loader->supportsRelationType('Contact'));
    static::assertFalse($this->loader->supportsRelationType('ContactX'));
  }

}
