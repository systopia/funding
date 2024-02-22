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
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetTemplatesAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
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

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->actionHandler = new GetTemplatesActionHandler(
      $this->api4Mock,
      $this->applicationProcessManagerMock,
      $this->fundingCaseManagerMock
    );
  }

  public function testGetTemplates(): void {
    $applicationProcess = ApplicationProcessFactory::createApplicationProcess(['id' => 2]);
    $this->applicationProcessManagerMock->method('get')
      ->with(2)
      ->willReturn($applicationProcess);
    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->method('get')
      ->with($fundingCase->getId())
      ->willReturn($fundingCase);

    $this->api4Mock->method('execute')
      ->with(FundingApplicationCiviOfficeTemplate::getEntityName(), 'get', [
        'select' => ['id', 'label'],
        'where' => [['case_type_id', '=', $fundingCase->getFundingCaseTypeId()]],
        'orderBy' => ['label' => 'ASC'],
      ])
      ->willReturn(new Result([['id' => 3, 'label' => 'test']]));

    $action = $this->createApi4ActionMock(GetTemplatesAction::class)
      ->setApplicationProcessId(2);

    static::assertSame([['id' => 3, 'label' => 'test']], $this->actionHandler->getTemplates($action));
  }

  public function testGetTemplatesNoApplicationProcess(): void {
    $this->applicationProcessManagerMock->method('get')
      ->with(2)
      ->willReturn(NULL);

    $action = $this->createApi4ActionMock(GetTemplatesAction::class)
      ->setApplicationProcessId(2);

    static::assertSame([], $this->actionHandler->getTemplates($action));
  }

}
