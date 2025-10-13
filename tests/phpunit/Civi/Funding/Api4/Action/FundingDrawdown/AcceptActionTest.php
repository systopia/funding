<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingDrawdown;

use Civi\Api4\Generic\Result;
use Civi\Funding\EntityFactory\DrawdownBundleFactory;
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\Traits\CreateMockTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\FundingDrawdown\AcceptAction
 */
final class AcceptActionTest extends TestCase {

  use CreateMockTrait;

  private AcceptAction $action;

  /**
   * @var \Civi\Funding\PayoutProcess\DrawdownManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private $drawdownManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->drawdownManagerMock = $this->createMock(DrawdownManager::class);
    $this->action = $this->createApi4ActionMock(
      AcceptAction::class,
      $this->drawdownManagerMock,
      TestRequestContext::newInternal(2),
    );
  }

  public function testRun(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    $drawdown = $drawdownBundle->getDrawdown();

    $this->drawdownManagerMock->method('getBundle')
      ->with($drawdown->getId())
      ->willReturn($drawdownBundle);

    $this->drawdownManagerMock->expects(static::once())->method('accept')
      ->with($drawdownBundle, 2);

    $this->action->setId($drawdown->getId());
    $result = new Result();
    $this->action->_run($result);

    static::assertSame([$drawdown->toArray()], $result->getArrayCopy());
  }

}
