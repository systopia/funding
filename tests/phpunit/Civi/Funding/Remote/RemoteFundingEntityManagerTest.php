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

/**
 * @noinspection PropertyAnnotationInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\Funding\Remote;

use Civi\API\Exception\NotImplementedException;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Mock\Api4\Action\RemoteActionMock;
use Civi\Funding\Mock\Api4\Action\StandardActionMock;
use Civi\Funding\Mock\Api4\Action\StandardWithContactIdActionMock;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Remote\RemoteFundingEntityManager
 */
final class RemoteFundingEntityManagerTest extends TestCase {

  /**
   * The first index is an entity name, the second index is an action name.
   *
   * @var array<string, array<string, AbstractAction>>
   */
  private array $actions;

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface|\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\Remote\RemoteFundingEntityManager
   */
  private RemoteFundingEntityManager $entityManager;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->entityManager = new RemoteFundingEntityManager($this->api4Mock);
    $this->actions = [];

    $this->api4Mock->method('createAction')->willReturnCallback(function (string $entity, string $action) {
      if (isset($this->actions[$entity][$action])) {
        return $this->actions[$entity][$action];
      }

      throw new NotImplementedException(sprintf('%s::%s', $entity, $action));
    });
  }

  public function testGetById(): void {
    $action = new RemoteActionMock();
    $this->addMockAction('RemoteFoo', 'get', $action);

    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $record = ['id' => 11, 'foo' => 'bar'];
    $apiResult->exchangeArray([$record]);
    $this->api4Mock->expects(static::once())->method('execute')
      ->with('RemoteFoo', 'get', [
        'where' => [['id', '=', 11]],
        'remoteContactId' => '00',
      ])->willReturn($apiResult);

    static::assertSame($record, $this->entityManager->getById('RemoteFoo', 11, '00', 2));
  }

  public function testGetByIdNotFound(): void {
    $action = new RemoteActionMock();
    $this->addMockAction('RemoteFoo', 'get', $action);

    $apiResult = new Result();
    $apiResult->rowCount = 0;
    $this->api4Mock->expects(static::once())->method('execute')
      ->with('RemoteFoo', 'get', [
        'where' => [['id', '=', 11]],
        'remoteContactId' => '00',
      ])->willReturn($apiResult);

    static::assertNull($this->entityManager->getById('RemoteFoo', 11, '00', 2));
  }

  public function testGetByIdNonRemote(): void {
    $this->addMockAction('RemoteFoo', 'get', new RemoteActionMock());
    $this->addMockAction('Foo', 'get', new StandardActionMock());

    $accessCheckResult = new Result();
    $accessCheckResult->rowCount = 1;
    $accessCheckResult->exchangeArray([['id' => 11]]);

    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $record = ['id' => 11, 'foo' => 'bar'];
    $apiResult->exchangeArray([$record]);

    $valueMap = [
      [
        'RemoteFoo',
        'get',
        ['select' => ['id'], 'where' => [['id', '=', 11]], 'remoteContactId' => '00'],
        $accessCheckResult,
      ],
      ['Foo', 'get', ['where' => [['id', '=', 11]]], $apiResult],
    ];

    $this->api4Mock->expects(static::exactly(2))->method('execute')
      ->willReturnMap($valueMap);

    static::assertSame($record, $this->entityManager->getById('Foo', 11, '00', 2));
  }

  public function testGetByIdNonRemoteWithContactId(): void {
    $this->addMockAction('Foo', 'get', new StandardWithContactIdActionMock());

    $accessCheckResult = new Result();
    $accessCheckResult->rowCount = 1;
    $accessCheckResult->exchangeArray([['id' => 11]]);

    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $record = ['id' => 11, 'foo' => 'bar'];
    $apiResult->exchangeArray([$record]);

    $valueMap = [
      ['Foo', 'get', ['where' => [['id', '=', 11]]], $apiResult],
    ];

    $this->api4Mock->expects(static::once())->method('execute')
      ->willReturnMap($valueMap);

    static::assertSame($record, $this->entityManager->getById('Foo', 11, '00', 2));
  }

  public function testGetByIdInvalidEntity(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown entity "Foo"');
    $this->entityManager->getById('Foo', 11, '00', 2);
  }

  public function testGetByIdInvalidRemoteEntity(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown entity "RemoteFoo"');
    $this->entityManager->getById('RemoteFoo', 11, '00', 2);
  }

  public function testHasAccessTrue(): void {
    $this->addMockAction('RemoteFoo', 'get', new RemoteActionMock());

    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $apiResult->exchangeArray([['id' => 11]]);
    $this->api4Mock->expects(static::once())->method('execute')
      ->with('RemoteFoo', 'get', ['select' => ['id'], 'where' => [['id', '=', 11]], 'remoteContactId' => '00'])
      ->willReturn($apiResult);

    static::assertTrue($this->entityManager->hasAccess('RemoteFoo', 11, '00', 2));
  }

  public function testHasAccessFalse(): void {
    $this->addMockAction('RemoteFoo', 'get', new RemoteActionMock());

    $apiResult = new Result();
    $apiResult->rowCount = 0;
    $this->api4Mock->expects(static::once())->method('execute')
      ->with('RemoteFoo', 'get', ['select' => ['id'], 'where' => [['id', '=', 11]], 'remoteContactId' => '00'])
      ->willReturn($apiResult);

    static::assertFalse($this->entityManager->hasAccess('RemoteFoo', 11, '00', 2));
  }

  public function testHasAccessNonRemote(): void {
    $this->addMockAction('Foo', 'get', new StandardActionMock());

    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $apiResult->exchangeArray([['id' => 11]]);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with('Foo', 'get', ['select' => ['id'], 'where' => [['id', '=', 11]]])
      ->willReturn($apiResult);

    static::assertTrue($this->entityManager->hasAccess('Foo', 11, '00', 2));
  }

  public function testHasAccessNonRemoteWithContactId(): void {
    $this->addMockAction('Foo', 'get', new StandardWithContactIdActionMock());

    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $apiResult->exchangeArray([['id' => 11]]);

    $this->api4Mock->expects(static::once())->method('execute')
      ->with('Foo', 'get', ['select' => ['id'], 'where' => [['id', '=', 11]]])
      ->willReturn($apiResult);

    static::assertTrue($this->entityManager->hasAccess('Foo', 11, '00', 2));
  }

  public function testHasAccessInvalidEntity(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown entity "Foo"');
    $this->entityManager->hasAccess('Foo', 11, '00', 2);
  }

  public function testHasAccessInvalidRemoteEntity(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Unknown entity "RemoteFoo"');
    $this->entityManager->hasAccess('RemoteFoo', 11, '00', 2);
  }

  private function addMockAction(string $entity, string $actionName, AbstractAction $action): self {
    $this->actions[$entity][$actionName] = $action;

    return $this;
  }

}
