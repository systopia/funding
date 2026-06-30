<?php

declare(strict_types = 1);

namespace Civi\Funding\FundingProgram\Api4\ActionHandler;

use Civi\Api4\FundingCaseTypeProgram;
use Civi\Api4\FundingFormStringTranslation;
use Civi\Api4\FundingNewCasePermissions;
use Civi\Api4\FundingProgram;
use Civi\Api4\FundingProgramContactRelation;
use Civi\Api4\FundingRecipientContactRelation;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\FundingProgram\CloneAction;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingProgram\Api4\ActionHandler\CloneHandler
 */
final class CloneHandlerTest extends TestCase {

  public function testPrepareTargetFundingProgramData(): void {
    $api4 = $this->createMock(Api4Interface::class);
    $handler = new CloneHandler($api4);

    $action = $this->getMockBuilder(CloneAction::class)
      ->setConstructorArgs([$api4])
      ->onlyMethods(['getBatchRecords'])
      ->addMethods(['getValues', 'getCheckPermissions'])
      ->getMock();
    $action->method('getBatchRecords')->willReturn([
      [
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
      ],
    ]);
    $action->method('getValues')->willReturn([]);
    $action->method('getCheckPermissions')->willReturn(FALSE);

    $expectedParams = [
      'title' => 'Copy of Original Program',
      'abbreviation' => 'OP_copy',
      'identifier_prefix' => 'OP-',
      'start_date' => '2026-01-01',
      'end_date' => '2026-12-31',
      'requests_start_date' => '2026-01-01',
      'requests_end_date' => '2026-06-01',
      'currency' => 'EUR',
      'budget' => 1000.0,
      'custom_123' => 'custom value',
    ];

    $programClone = [
      'id' => 124,
      'title' => 'Copy of Original Program',
      'abbreviation' => 'OP_copy',
      'custom_123' => 'custom value',
    ];
    $api4->expects(static::once())
      ->method('createEntity')
      ->with(FundingProgram::getEntityName(), $expectedParams)
      ->willReturn(new Result([$programClone]));

    $result = $handler->clone($action);

    static::assertSame([$programClone], $result);
  }

  public function testClone(): void {
    $api4 = $this->createMock(Api4Interface::class);
    $handler = new CloneHandler($api4);

    $action = $this->getMockBuilder(CloneAction::class)
      ->setConstructorArgs([$api4])
      ->onlyMethods(['getBatchRecords'])
      ->addMethods(['getValues', 'getCheckPermissions'])
      ->getMock();
    $action->method('getBatchRecords')->willReturn([
      ['id' => 123, 'title' => 'Original', 'abbreviation' => 'OR', 'currency' => 'EUR'],
    ]);
    $action->method('getValues')->willReturn([
      'title' => 'New Title',
      'currency' => 'USD',
      'custom_data' => 'new',
    ]);
    $action->method('getCheckPermissions')->willReturn(FALSE);

    $programClone = [
      'id' => 124,
      'title' => 'New Title',
      'abbreviation' => 'OR_copy',
      'currency' => 'USD',
      'custom_data' => 'new',
    ];
    $createResult = new Result([$programClone]);
    $expectedParams = [
      'title' => 'New Title',
      'abbreviation' => 'OR_copy',
      'currency' => 'USD',
      'custom_data' => 'new',
    ];
    $api4->expects(static::once())
      ->method('createEntity')
      ->with(FundingProgram::getEntityName(), $expectedParams)
      ->willReturn($createResult);

    $getResult = new Result([]);

    $callCount = 0;
    $api4->expects(static::exactly(6))
      ->method('execute')
      ->willReturnCallback(function($entity, $action, $params) use (&$callCount, $getResult) {
        $callCount++;
        static::assertEquals('get', $action);
        if ($callCount === 1) {
          static::assertEquals(FundingProgram::getEntityName(), $entity);
          static::assertEquals([
            'select' => ['row_count'],
            'where' => [['abbreviation', '=', 'OR_copy']],
            'checkPermissions' => FALSE,
          ], $params);
        }
        else {
          $expectedEntities = [
            2 => FundingCaseTypeProgram::getEntityName(),
            3 => FundingProgramContactRelation::getEntityName(),
            4 => FundingRecipientContactRelation::getEntityName(),
            5 => FundingNewCasePermissions::getEntityName(),
            6 => FundingFormStringTranslation::getEntityName(),
          ];
          static::assertEquals($expectedEntities[$callCount], $entity);
          static::assertEquals(['where' => [['funding_program_id', '=', 123]], 'checkPermissions' => FALSE], $params);
        }
        return $getResult;
      });

    $result = $handler->clone($action);

    static::assertSame([$programClone], $result);
  }

}
