<?php

declare(strict_types = 1);

namespace Civi\Funding\FundingProgram\Api4\ActionHandler;

use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\Result;
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

    $api4->expects(static::exactly(2))
      ->method('execute')
      ->willReturnCallback(function($entity, $action) {
        static::assertEquals(FundingProgram::getEntityName(), $entity);
        static::assertEquals('get', $action);

        $result = $this->createMock(Result::class);
        $result->method('count')->willReturn(0);
        return $result;
      });

    $targetData = $handler->prepareTargetFundingProgramData($sourceEntity, []);

    static::assertNotEquals(123, $targetData->toArray()['id'] ?? NULL);
    static::assertEquals('Copy of Original Program', $targetData->getTitle());
    static::assertEquals('OP_copy', $targetData->getAbbreviation());
    static::assertEquals('custom value', $targetData->toArray()['custom_123'] ?? NULL);
  }

}
