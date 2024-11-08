<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Remote\Api4\ActionHandler;

use Civi\Api4\FundingApplicationCiviOfficeTemplate;
use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetTemplatesAction;
use Civi\Funding\Traits\CreateMockTrait;
use Civi\RemoteTools\Api4\Api4Interface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Remote\Api4\ActionHandler\GetTemplatesActionHandler
 *
 * @phpstan-import-type applicationCiviOfficeTemplateT from \Civi\Api4\FundingApplicationCiviOfficeTemplate
 */
final class GetTemplatesActionHandlerTest extends TestCase {

  use CreateMockTrait;

  private GetTemplatesActionHandler $actionHandler;

  /**
   * @var \Civi\RemoteTools\Api4\Api4Interface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $api4Mock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->actionHandler = new GetTemplatesActionHandler($this->api4Mock);
  }

  public function testGetTemplates(): void {
    $series = [
      [
        [
          FundingApplicationProcess::getEntityName(),
          'get',
          [
            'select' => ['funding_case_id.funding_case_type_id'],
            'where' => [['id', '=', 2]],
          ],
        ],
        new Result([['funding_case_id.funding_case_type_id' => 123]]),
      ],
      [
        [
          FundingApplicationCiviOfficeTemplate::getEntityName(),
          'get',
          [
            'select' => ['id', 'label'],
            'where' => [['case_type_id', '=', 123]],
            'orderBy' => ['label' => 'ASC'],
          ],
        ],
        new Result([['id' => 3, 'label' => 'test']]),
      ],
    ];

    $this->api4Mock->method('execute')->willReturnCallback(function (...$args) use (&$series) {
      // @phpstan-ignore-next-line
      [$expectedArgs, $return] = array_shift($series);
      static::assertEquals($expectedArgs, $args);

      return $return;
    });

    $action = $this->createApi4ActionMock(GetTemplatesAction::class)
      ->setApplicationProcessId(2);

    static::assertSame([['id' => 3, 'label' => 'test']], $this->actionHandler->getTemplates($action));
  }

  public function testGetTemplatesNoApplicationProcess(): void {
    $this->api4Mock->method('execute')
      ->with(FundingApplicationProcess::getEntityName(), 'get', [
        'select' => ['funding_case_id.funding_case_type_id'],
        'where' => [['id', '=', 2]],
      ])->willReturn(new Result());

    $action = $this->createApi4ActionMock(GetTemplatesAction::class)
      ->setApplicationProcessId(2);

    static::assertSame([], $this->actionHandler->getTemplates($action));
  }

}
