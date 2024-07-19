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

use Civi\Funding\ApplicationProcess\ActionsContainer\ApplicationSubmitActionsContainer;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactory;
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

  private ApplicationSubmitActionsContainer $submitActionsContainer;

  private ApplicationSubmitActionsFactory $submitActionsFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(ApplicationProcessActionsDeterminerInterface::class);
    $this->submitActionsContainer = new ApplicationSubmitActionsContainer();
    $this->submitActionsFactory = new ApplicationSubmitActionsFactory(
      $this->actionsDeterminerMock,
      $this->submitActionsContainer,
    );
  }

  public function testCreateSubmitActions(): void {
    $this->submitActionsContainer->add('test1', 'Test1');
    $this->submitActionsContainer->add('test2', 'Test2');
    $this->submitActionsContainer->add('test3', 'Test3', 'Really?');
    $fullStatus = new FullApplicationProcessStatus('test', NULL, NULL);
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->actionsDeterminerMock->expects(static::once())->method('getActions')
      ->with($fullStatus, $statusList, ['permission'])
      ->willReturn(['test3', 'test1']);

    $submitActions = $this->submitActionsFactory->createSubmitActions($fullStatus, $statusList, ['permission']);
    // "test1" must be first
    static::assertSame([
      'test1' => ['label' => 'Test1', 'confirm' => NULL, 'properties' => []],
      'test3' => ['label' => 'Test3', 'confirm' => 'Really?', 'properties' => []],
    ], $submitActions);
  }

  public function testCreateSubmitActionsUnknownAction(): void {
    $this->submitActionsContainer->add('test1', 'Test1');
    $fullStatus = new FullApplicationProcessStatus('test', NULL, NULL);
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->actionsDeterminerMock->expects(static::once())->method('getActions')
      ->with($fullStatus, $statusList, ['permission'])
      ->willReturn(['test2']);

    static::assertSame([], $this->submitActionsFactory->createSubmitActions($fullStatus, $statusList, ['permission']));
  }

  public function testCreateInitialSubmitActions(): void {
    $this->submitActionsContainer->add('test1', 'Test1');
    $this->submitActionsContainer->add('test2', 'Test2');
    $this->submitActionsContainer->add('test3', 'Test3', 'Really?');
    $this->actionsDeterminerMock->expects(static::once())->method('getInitialActions')
      ->with(['permission'])
      ->willReturn(['test3', 'test1']);

    $submitActions = $this->submitActionsFactory->createInitialSubmitActions(['permission']);
    // "test1" must be first
    static::assertSame([
      'test1' => ['label' => 'Test1', 'confirm' => NULL, 'properties' => []],
      'test3' => ['label' => 'Test3', 'confirm' => 'Really?', 'properties' => []],
    ], $submitActions);
  }

  public function testCreateInitialSubmitActionsUnknownAction(): void {
    $this->submitActionsContainer->add('test1', 'Test1');
    $this->actionsDeterminerMock->expects(static::once())->method('getInitialActions')
      ->with(['permission'])
      ->willReturn(['test2']);

    static::assertSame([], $this->submitActionsFactory->createInitialSubmitActions(['permission']));
  }

}
