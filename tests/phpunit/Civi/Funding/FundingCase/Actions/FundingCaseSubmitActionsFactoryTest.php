<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Actions;

use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseAction;
use Civi\Funding\FundingCaseTypeServiceLocator;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use Civi\Funding\Mock\Psr\PsrContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Actions\FundingCaseSubmitActionsFactory
 */
final class FundingCaseSubmitActionsFactoryTest extends TestCase {

  private MockObject&FundingCaseActionsDeterminerInterface $actionsDeterminerMock;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private FundingCaseSubmitActionsFactory $submitActionsFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(FundingCaseActionsDeterminerInterface::class);
    $this->metaDataMock = new FundingCaseTypeMetaDataMock(FundingCaseTypeFactory::DEFAULT_NAME);
    $serviceLocatorContainer = new FundingCaseTypeServiceLocatorContainer(new PsrContainer([
      FundingCaseTypeFactory::DEFAULT_NAME => new FundingCaseTypeServiceLocator(new PsrContainer([
        FundingCaseActionsDeterminerInterface::class => $this->actionsDeterminerMock,
      ])),
    ]));
    $this->submitActionsFactory = new FundingCaseSubmitActionsFactory(
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock),
      $serviceLocatorContainer,
    );
  }

  public function testGetInitialSubmitActions(): void {
    $actionTest1 = new FundingCaseAction(['name' => 'test1', 'label' => 'Test1', 'priority' => 1]);
    $this->metaDataMock->addFundingCaseAction($actionTest1);
    $actionTest2 = new FundingCaseAction(['name' => 'test2', 'label' => 'Test2', 'priority' => 0]);
    $this->metaDataMock->addFundingCaseAction($actionTest2);

    $this->actionsDeterminerMock->expects(static::once())->method('getInitialActions')
      ->with(['permission'])
      ->willReturn(['test2', 'test1']);

    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $submitActions = $this->submitActionsFactory->getInitialSubmitActions(['permission'], $fundingCaseType);
    // "test1" must be first
    static::assertSame([
      'test1' => $actionTest1,
      'test2' => $actionTest2,
    ], $submitActions);
  }

  public function testGetInitialSubmitActionsUnknownAction(): void {
    $this->actionsDeterminerMock->expects(static::once())->method('getInitialActions')
      ->with(['permission'])
      ->willReturn(['test']);

    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    static::assertSame([], $this->submitActionsFactory->getInitialSubmitActions(['permission'], $fundingCaseType));
  }

  public function testGetSubmitActions(): void {
    $actionTest1 = new FundingCaseAction(['name' => 'test1', 'label' => 'Test1', 'priority' => 1]);
    $this->metaDataMock->addFundingCaseAction($actionTest1);
    $actionTest2 = new FundingCaseAction(['name' => 'test2', 'label' => 'Test2', 'priority' => 0]);
    $this->metaDataMock->addFundingCaseAction($actionTest2);

    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->actionsDeterminerMock->expects(static::once())->method('getActions')
      ->with(
        $fundingCaseBundle->getFundingCase()->getStatus(),
        $statusList,
        $fundingCaseBundle->getFundingCase()->getPermissions()
      )
      ->willReturn(['test2', 'test1']);

    $submitActions = $this->submitActionsFactory->getSubmitActions(
      $fundingCaseBundle,
      $statusList
    );
    // "test1" must be first
    static::assertSame([
      'test1' => $actionTest1,
      'test2' => $actionTest2,
    ], $submitActions);
  }

  public function testGetSubmitActionsUnknownAction(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create();
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->actionsDeterminerMock->expects(static::once())->method('getActions')
      ->with(
        $fundingCaseBundle->getFundingCase()->getStatus(),
        $statusList,
        $fundingCaseBundle->getFundingCase()->getPermissions()
      )
      ->willReturn(['test']);

    static::assertSame(
      [],
      $this->submitActionsFactory->getSubmitActions($fundingCaseBundle, $statusList)
    );
  }

}
