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

namespace Civi\Funding\PayoutProcess\Validator;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Validator\DrawdownCreateValidator
 */
final class DrawdownCreateValidatorTest extends TestCase {

  private FundingCaseEntity $fundingCase;

  private PayoutProcessEntity $payoutProcess;

  private DrawdownCreateValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);

    $this->validator = new DrawdownCreateValidator(
      $fundingCaseManagerMock,
      $payoutProcessManagerMock,
    );

    $this->payoutProcess = PayoutProcessFactory::create();
    $payoutProcessManagerMock->method('get')->with(PayoutProcessFactory::DEFAULT_ID)->willReturn($this->payoutProcess);
    $this->fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseManagerMock->method('get')->with(FundingCaseFactory::DEFAULT_ID)->willReturn($this->fundingCase);
  }

  public function testGetEntityClass(): void {
    static::assertSame(DrawdownEntity::class, $this->validator::getEntityClass());
  }

  public function testValidate(): void {
    $new = DrawdownFactory::create();
    $current = DrawdownFactory::create();

    static::assertTrue($this->validator->validate($new, $current)->isValid());
  }

  public function testValidateNewWithoutPermission(): void {
    $new = DrawdownFactory::create();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to create drawdown is missing.');

    static::assertTrue($this->validator->validateNew($new)->isValid());
  }

  public function testValidateNewWithPermission(): void {
    $new = DrawdownFactory::create();

    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());
    static::assertTrue($this->validator->validateNew($new)->isValid());
  }

}
