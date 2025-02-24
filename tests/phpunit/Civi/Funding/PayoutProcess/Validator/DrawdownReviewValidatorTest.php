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
 * @covers \Civi\Funding\PayoutProcess\Validator\DrawdownReviewValidator
 */
final class DrawdownReviewValidatorTest extends TestCase {

  private FundingCaseEntity $fundingCase;

  private PayoutProcessEntity $payoutProcess;

  private DrawdownReviewValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);

    $this->validator = new DrawdownReviewValidator(
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

  public function testValidateUnchanged(): void {
    $new = DrawdownFactory::create(['status' => 'accepted']);
    $current = DrawdownFactory::create(['status' => 'accepted']);

    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateWithoutPermission(): void {
    $new = DrawdownFactory::create(['status' => 'new']);
    $current = DrawdownFactory::create(['status' => 'accepted']);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to change drawdown status is missing.');
    $this->validator->validate($new, $current, TRUE)->isValid();
  }

  public function testValidateWithPermission(): void {
    $new = DrawdownFactory::create(['status' => 'new']);
    $current = DrawdownFactory::create(['status' => 'accepted']);

    $this->fundingCase->setValues(['permissions' => ['review_drawdown']] + $this->fundingCase->toArray());
    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateNew(): void {
    $new = DrawdownFactory::create(['status' => 'new']);
    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateNewWithoutPermission(): void {
    $new = DrawdownFactory::create(['status' => 'accepted']);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to change drawdown status is missing.');
    $this->validator->validateNew($new, TRUE)->isValid();
  }

  public function testValidateNewWithPermission(): void {
    $new = DrawdownFactory::create(['status' => 'accepted']);

    $this->fundingCase->setValues(['permissions' => ['review_drawdown']] + $this->fundingCase->toArray());
    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

}
