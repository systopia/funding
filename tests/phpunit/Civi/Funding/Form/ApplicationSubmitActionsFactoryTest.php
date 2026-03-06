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

namespace Civi\Funding\Form;

use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactory;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessAction;
use Civi\Funding\FundingCaseTypeServiceLocator;
use Civi\Funding\FundingCaseTypeServiceLocatorContainer;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use Civi\Funding\Mock\Psr\PsrContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Form\Application\ApplicationSubmitActionsFactory
 */
final class ApplicationSubmitActionsFactoryTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionsDeterminerMock;

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private ApplicationSubmitActionsFactory $submitActionsFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(ApplicationProcessActionsDeterminerInterface::class);
    $this->metaDataMock = new FundingCaseTypeMetaDataMock(FundingCaseTypeFactory::DEFAULT_NAME);
    $serviceLocatorContainer = new FundingCaseTypeServiceLocatorContainer(new PsrContainer([
      FundingCaseTypeFactory::DEFAULT_NAME => new FundingCaseTypeServiceLocator(new PsrContainer([
        ApplicationProcessActionsDeterminerInterface::class => $this->actionsDeterminerMock,
      ])),
    ]));
    $this->submitActionsFactory = new ApplicationSubmitActionsFactory(
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock),
      $serviceLocatorContainer,
    );
  }

  public function testGetSubmitActions(): void {
    $actionTest1 = new ApplicationProcessAction(['name' => 'test1', 'label' => 'Test1']);
    $this->metaDataMock->addApplicationProcessAction($actionTest1);
    $actionTest2 = new ApplicationProcessAction(['name' => 'test2', 'label' => 'Test2']);
    $this->metaDataMock->addApplicationProcessAction($actionTest2);

    $applicationProcessBundle = $this->createApplicationProcessBundle('test', NULL, NULL);
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->actionsDeterminerMock->expects(static::once())->method('getActions')
      ->with($applicationProcessBundle, $statusList)
      ->willReturn(['test2', 'test1']);

    $submitActions = $this->submitActionsFactory->getSubmitActions(
      $applicationProcessBundle,
      $statusList
    );
    // "test1" must be first
    static::assertSame([
      'test1' => $actionTest1,
      'test2' => $actionTest2,
    ], $submitActions);
  }

  public function testGetSubmitActionsUnknownAction(): void {
    $applicationProcessBundle = $this->createApplicationProcessBundle('test', NULL, NULL);
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->actionsDeterminerMock->expects(static::once())->method('getActions')
      ->with($applicationProcessBundle, $statusList)
      ->willReturn(['test']);

    static::assertSame(
      [],
      $this->submitActionsFactory->getSubmitActions($applicationProcessBundle, $statusList)
    );
  }

  public function testGetInitialSubmitActions(): void {
    $actionTest1 = new ApplicationProcessAction(['name' => 'test1', 'label' => 'Test1']);
    $this->metaDataMock->addApplicationProcessAction($actionTest1);
    $actionTest2 = new ApplicationProcessAction(['name' => 'test2', 'label' => 'Test2']);
    $this->metaDataMock->addApplicationProcessAction($actionTest2);

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

  private function createApplicationProcessBundle(
    string $status,
    ?bool $isReviewCalculative,
    ?bool $isReviewContent
  ): ApplicationProcessEntityBundle {
    return ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => $status,
      'is_review_calculative' => $isReviewCalculative,
      'is_review_content' => $isReviewContent,
    ]);
  }

}
