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

namespace Civi\Funding\Contact\RecipientsLoader;

use Civi\Api4\FundingRecipientContactRelation;
use Civi\Api4\Generic\AbstractGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Contact\RelatedContactsLoaderInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Contact\RecipientsLoader\DefaultPossibleRecipientsLoader
 *
 * @phpstan-type contactRelationT array{
 *   id: int,
 *   type: string,
 *   properties: array<string, mixed>,
 * }
 */
final class DefaultPossibleRecipientsLoaderTest extends TestCase {

  /**
   * \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  /**
   * @var \Civi\Funding\Contact\RelatedContactsLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $relatedContactsLoaderMock;

  private DefaultPossibleRecipientsLoader $recipientsLoader;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->relatedContactsLoaderMock = $this->createMock(RelatedContactsLoaderInterface::class);
    $this->recipientsLoader = new DefaultPossibleRecipientsLoader(
      $this->api4Mock,
      $this->relatedContactsLoaderMock,
    );
  }

  public function testGetPossibleRecipientsSimple(): void {
    $contactRelation = [
      'id' => 1,
      'type' => 'test',
      'properties' => ['foo' => 'bar'],
    ];

    $this->mockApiContactRelationGet([$contactRelation]);

    $this->relatedContactsLoaderMock->expects(static::once())->method('getRelatedContacts')
      ->with(123, 'test', ['foo' => 'bar'])
      ->willReturn([2 => ['display_name' => 'Name']]);

    static::assertSame([2 => 'Name'], $this->recipientsLoader->getPossibleRecipients(123));
  }

  public function testGetPossibleRecipientsMultipleRelations(): void {
    $contactRelation1 = [
      'id' => 1,
      'type' => 'test1',
      'properties' => ['foo1' => 'bar1'],

    ];
    $contactRelation2 = [
      'id' => 2,
      'type' => 'test2',
      'properties' => ['foo2' => 'bar2'],
    ];

    $this->mockApiContactRelationGet([$contactRelation1, $contactRelation2]);

    $this->relatedContactsLoaderMock->expects(static::exactly(2))->method('getRelatedContacts')
      ->withConsecutive(
        [123, 'test1', ['foo1' => 'bar1']],
        [123, 'test2', ['foo2' => 'bar2']],
      )
      ->willReturnOnConsecutiveCalls(
        [
          1 => ['display_name' => 'Name1'],
          2 => ['display_name' => 'Name2'],
        ],
        [
          2 => ['display_name' => 'Name2_2'],
          3 => ['display_name' => 'Name3'],
        ],
      );

    static::assertSame(
      [1 => 'Name1' , 2 => 'Name2', 3 => 'Name3'],
      $this->recipientsLoader->getPossibleRecipients(123)
    );
  }

  public function testGetPossibleRecipientsIgnoresDisplayNameNull(): void {
    $contactRelation = [
      'id' => 1,
      'type' => 'test',
      'properties' => ['foo' => 'bar'],
    ];

    $this->mockApiContactRelationGet([$contactRelation]);

    $this->relatedContactsLoaderMock->expects(static::once())->method('getRelatedContacts')
      ->with(123, 'test', ['foo' => 'bar'])
      ->willReturn([2 => ['display_name' => NULL]]);

    static::assertSame([], $this->recipientsLoader->getPossibleRecipients(123));
  }

  /**
   * @phpstan-param array<contactRelationT> $contactRelations
   */
  private function mockApiContactRelationGet(array $contactRelations): void {
    $this->api4Mock->expects(static::once())->method('executeAction')
      ->with(static::callback(function (AbstractGetAction $action) {
        static::assertSame(FundingRecipientContactRelation::_getEntityName(), $action->getEntityName());

        return TRUE;
      }))->willReturn(new Result($contactRelations));
  }

}
