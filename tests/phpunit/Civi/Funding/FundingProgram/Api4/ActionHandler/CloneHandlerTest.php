<?php

declare(strict_types = 1);

namespace Civi\Funding\FundingProgram\Api4\ActionHandler;

use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\FundingProgram\CloneAction;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingProgram\Api4\ActionHandler\CloneHandler
 */
final class CloneHandlerTest extends TestCase {

  public function testPrepareTargetFundingProgramData(): void {
    $api4 = $this->createMock(Api4Interface::class);
    $handler = new CloneHandler($api4);

    $sourceData = [
      'id' => 123,
      'title' => 'Original Program',
      'abbreviation' => 'OP',
      'identifier_prefix' => 'OP-',
      'start_date' => '2026-01-01',
      'end_date' => '2026-12-31',
      'requests_start_date' => '2026-01-01',
      'requests_end_date' => '2026-06-01',
      'currency' => 'EUR',
      'budget' => 1000.0,
      'custom_123' => 'custom value',
    ];

    $sourceEntity = FundingProgramEntity::fromArray($sourceData);

    $result = $this->createMock(Result::class);
    $result->method('count')->willReturn(0);

    $api4->expects(static::exactly(2))
      ->method('execute')
      ->with(
        static::equalTo(FundingProgram::getEntityName()),
        static::equalTo('get'),
        static::isType('array')
      )
      ->willReturn($result);

    $targetData = $handler->prepareTargetFundingProgramData($sourceEntity, []);

    static::assertNotEquals(123, $targetData->toArray()['id'] ?? NULL);
    static::assertEquals('Copy of Original Program', $targetData->getTitle());
    static::assertEquals('OP_copy', $targetData->getAbbreviation());
    static::assertEquals('custom value', $targetData->toArray()['custom_123'] ?? NULL);
  }

  public function testClone(): void {
    $api4 = $this->createMock(Api4Interface::class);
    $handler = new CloneHandler($api4);

    $action = $this->getMockBuilder(CloneAction::class)
      ->setConstructorArgs([$api4])
      ->onlyMethods(['getBatchRecords'])
      ->addMethods(['getValues', 'getCheckPermissions'])
      ->getMock();
    $action->method('getBatchRecords')->willReturn([['id' => 123, 'title' => 'Original', 'abbreviation' => 'OR']]);
    $action->method('getValues')->willReturn(['title' => 'Clone']);
    $action->method('getCheckPermissions')->willReturn(FALSE);

    $createResult = $this->createMock(Result::class);
    $createResult->method('single')->willReturn(['id' => 124, 'title' => 'Clone', 'abbreviation' => 'OR']);
    $api4->expects(static::once())
      ->method('createEntity')
      ->willReturn($createResult);

    $getResult = $this->createMock(Result::class);
    $getResult->method('getIterator')->willReturn(new \ArrayIterator([]));

    $api4->expects(static::exactly(6))
      ->method('execute')
      ->with(
        static::isType('string'),
        static::equalTo('get'),
        static::isType('array')
      )
      ->willReturn($getResult);

    $result = $handler->clone($action);

    static::assertCount(1, $result);
    static::assertEquals(124, $result[0]['id']);
  }

}
