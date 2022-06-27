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
use Civi\Api4\Generic\Result;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub\Exception as StubException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Remote\RemoteFundingEntityManager
 */
final class RemoteFundingEntityManagerTest extends TestCase {

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
  }

  public function testGetInstance(): void {
    static::assertNotNull(RemoteFundingEntityManager::getInstance());
  }

  public function testGetById(): void {
    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $record = ['id' => 11, 'foo' => 'bar'];
    $apiResult->exchangeArray([$record]);
    $this->api4Mock->expects(static::once())->method('execute')
      ->with('RemoteFoo', 'get', [
        'where' => ['id', '=', 11],
        'remoteContactId' => '00',
      ])->willReturn($apiResult);

    static::assertSame($record, $this->entityManager->getById('RemoteFoo', 11, '00'));
  }

  public function testGetByIdNotFound(): void {
    $apiResult = new Result();
    $apiResult->rowCount = 0;
    $this->api4Mock->expects(static::once())->method('execute')
      ->with('RemoteFoo', 'get', [
        'where' => ['id', '=', 11],
        'remoteContactId' => '00',
      ])->willReturn($apiResult);

    static::assertNull($this->entityManager->getById('RemoteFoo', 11, '00'));
  }

  public function testGetByIdNonRemote(): void {

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
        ['select' => ['id'], 'where' => ['id', '=', 11], 'remoteContactId' => '00'],
        $accessCheckResult,
      ],
      ['Foo', 'get', ['where' => ['id', '=', 11]], $apiResult],
    ];

    $this->api4Mock->expects(static::exactly(2))->method('execute')
      ->willReturnMap($valueMap);

    static::assertSame($record, $this->entityManager->getById('Foo', 11, '00'));
  }

  public function testGetByIdInvalidEntity(): void {
    $fooNotImplementedException = new NotImplementedException('Foo');
    $this->api4Mock->expects(static::exactly(2))->method('execute')
      ->withConsecutive(
        [
          'RemoteFoo',
          'get',
          ['select' => ['id'], 'where' => ['id', '=', 11], 'remoteContactId' => '00'],
        ],
        ['Foo', 'get', ['where' => ['id', '=', 11]]],
      )
      ->willReturnOnConsecutiveCalls(
        new StubException(new NotImplementedException('RemoteFoo')),
        new StubException($fooNotImplementedException)
      );

    $this->expectExceptionObject(
      new \InvalidArgumentException('Unknown entity "Foo"', 0, $fooNotImplementedException)
    );
    $this->entityManager->getById('Foo', 11, '00');
  }

  public function testGetByIdInvalidRemoteEntity(): void {
    $remoteFooNotImplementedException = new NotImplementedException('RemoteFoo');
    $this->api4Mock->expects(static::once())->method('execute')
      ->with(
          'RemoteFoo',
          'get',
          ['where' => ['id', '=', 11], 'remoteContactId' => '00']
      )
      ->willThrowException($remoteFooNotImplementedException);

    $this->expectExceptionObject(
      new \InvalidArgumentException('Unknown entity "RemoteFoo"', 0, $remoteFooNotImplementedException)
    );
    $this->entityManager->getById('RemoteFoo', 11, '00');
  }

  public function testHasAccessTrue(): void {
    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $apiResult->exchangeArray([['id' => 11]]);
    $this->api4Mock->expects(static::once())->method('execute')
      ->with('RemoteFoo', 'get', ['select' => ['id'], 'where' => ['id', '=', 11], 'remoteContactId' => '00'])
      ->willReturn($apiResult);

    static::assertTrue($this->entityManager->hasAccess('RemoteFoo', 11, '00'));
  }

  public function testHasAccessFalse(): void {
    $apiResult = new Result();
    $apiResult->rowCount = 0;
    $this->api4Mock->expects(static::once())->method('execute')
      ->with('RemoteFoo', 'get', ['select' => ['id'], 'where' => ['id', '=', 11], 'remoteContactId' => '00'])
      ->willReturn($apiResult);

    static::assertFalse($this->entityManager->hasAccess('RemoteFoo', 11, '00'));
  }

  public function testHasAccessNonRemote(): void {
    $apiResult = new Result();
    $apiResult->rowCount = 1;
    $apiResult->exchangeArray([['id' => 11]]);

    $this->api4Mock->expects(static::exactly(2))->method('execute')
      ->withConsecutive(
        [
          'RemoteFoo',
          'get',
          ['select' => ['id'], 'where' => ['id', '=', 11], 'remoteContactId' => '00'],
        ],
        ['Foo', 'get', ['select' => ['id'], 'where' => ['id', '=', 11]]])
      ->willReturnOnConsecutiveCalls(
        new StubException(new NotImplementedException('RemoteFoo')),
        $apiResult
      );

    static::assertTrue($this->entityManager->hasAccess('Foo', 11, '00'));
  }

  public function testHasAccessInvalidEntity(): void {
    $fooNotImplementedException = new NotImplementedException('Foo');

    $this->api4Mock->expects(static::exactly(2))->method('execute')
      ->withConsecutive(
        [
          'RemoteFoo',
          'get',
          ['select' => ['id'], 'where' => ['id', '=', 11], 'remoteContactId' => '00'],
        ],
        ['Foo', 'get', ['select' => ['id'], 'where' => ['id', '=', 11]]])
      ->willReturnOnConsecutiveCalls(
        new StubException(new NotImplementedException('RemoteFoo')),
        new StubException($fooNotImplementedException)
      );

    static::expectExceptionObject(
      new \InvalidArgumentException('Unknown entity "Foo"', 0, $fooNotImplementedException)
    );
    $this->entityManager->hasAccess('Foo', 11, '00');
  }

}
