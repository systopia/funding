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
use Civi\Funding\FundingCase\FundingCasePermissions;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Validation\EntityValidationError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Validator\DrawdownValidator
 */
final class DrawdownValidatorTest extends TestCase {

  private FundingCaseEntity $fundingCase;

  private PayoutProcessEntity $payoutProcess;

  /**
   * @var \Civi\Funding\PayoutProcess\PayoutProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $payoutProcessManagerMock;

  private DrawdownValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);

    $this->validator = new DrawdownValidator(
      $fundingCaseManagerMock,
      $this->payoutProcessManagerMock,
    );

    $this->payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('get')->with(PayoutProcessFactory::DEFAULT_ID)
      ->willReturn($this->payoutProcess);
    $this->fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseManagerMock->method('get')->with(FundingCaseFactory::DEFAULT_ID)->willReturn($this->fundingCase);
  }

  public function testGetEntityClass(): void {
    static::assertSame(DrawdownEntity::class, $this->validator::getEntityClass());
  }

  public function testValidate(): void {
    $new = DrawdownFactory::create();
    $current = DrawdownFactory::create();

    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateNewWithoutPermission(): void {
    $new = DrawdownFactory::create();

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to create drawdown is missing.');

    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateNewWithPermission(): void {
    $new = DrawdownFactory::create();
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($this->payoutProcess)
      ->willReturn(10.1);

    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateNewWithReviewDrawdownCreatePermission(): void {
    $new = DrawdownFactory::create();
    $this->fundingCase->setValues(['permissions' => ['review_drawdown_create']] + $this->fundingCase->toArray());

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($this->payoutProcess)
      ->willReturn(10.1);

    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateNewClosed(): void {
    $new = DrawdownFactory::create();
    $this->payoutProcess->setValues(['status' => 'closed'] + $this->payoutProcess->toArray());
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Payout process is closed.');

    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateAmountLessThanZero(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => -0.1]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $result = $this->validator->validate($new, $current, TRUE);
    static::assertFalse($result->isValid());
    static::assertEquals([
      'amount' => [
        EntityValidationError::new(
          'amount',
          'Requested amount is less than 0.'),
      ],
    ], $result->getErrors());
  }

  public function testValidateAmountLessThanZeroWithReviewFinishPermission(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => -0.1]);
    $this->fundingCase->setValues([
      'permissions' => [FundingCasePermissions::REVIEW_FINISH],
    ] + $this->fundingCase->toArray());

    $result = $this->validator->validate($new, $current, TRUE);
    static::assertTrue($result->isValid());
  }

  public function testValidateAmountZero(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => 0.0]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateAmountUnchanged(): void {
    $current = DrawdownFactory::create(['status' => 'new']);
    $new = DrawdownFactory::create(['status' => 'accepted']);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateAmountReduced(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => 9.9]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateAmountIncreased(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => 10.1]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($this->payoutProcess)
      ->willReturn(0.1);

    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateAmountExceedsLimit(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => 10.1]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($this->payoutProcess)
      ->willReturn(0.01);

    $result = $this->validator->validate($new, $current, TRUE);
    static::assertFalse($result->isValid());
    static::assertEquals([
      'amount' => [
        EntityValidationError::new(
          'amount',
          'Requested amount is greater than available amount.'),
      ],
    ], $result->getErrors());
  }

  public function testValidatePayoutProcessClosed(): void {
    $current = DrawdownFactory::create();
    $new = DrawdownFactory::create(['status' => 'accepted']);
    $this->payoutProcess->setStatus('closed');
    $this->fundingCase->setValues(['permissions' => ['review_drawdown']] + $this->fundingCase->toArray());

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Payout process is closed.');

    $this->validator->validate($new, $current, TRUE);
  }

  public function testValidatePayoutProcessClosedWithReviewFinishPermission(): void {
    $current = DrawdownFactory::create();
    $new = DrawdownFactory::create(['status' => 'accepted']);
    $this->payoutProcess->setStatus('closed');
    $this->fundingCase->setValues(
      ['permissions' => [FundingCasePermissions::REVIEW_FINISH]] + $this->fundingCase->toArray()
    );

    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateNew(): void {
    $new = DrawdownFactory::create(['amount' => 10.1]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($this->payoutProcess)
      ->willReturn(10.1);

    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateNewAmountLessThanZero(): void {
    $new = DrawdownFactory::create(['amount' => -0.1]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $result = $this->validator->validateNew($new, TRUE);
    static::assertFalse($result->isValid());
    static::assertEquals([
      'amount' => [
        EntityValidationError::new(
          'amount',
          'Requested amount is less than 0.'),
      ],
    ], $result->getErrors());
  }

  public function testValidateNewAmountLessThanZeroWithReviewFinishPermission(): void {
    $new = DrawdownFactory::create(['amount' => -0.1]);
    $this->fundingCase->setValues([
      'permissions' => [FundingCasePermissions::REVIEW_FINISH],
    ] + $this->fundingCase->toArray());

    $result = $this->validator->validateNew($new, TRUE);
    static::assertTrue($result->isValid());
  }

  public function testValidateNewAmountZero(): void {
    $new = DrawdownFactory::create(['amount' => 0.0]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($this->payoutProcess)
      ->willReturn(10.1);

    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateNewExceedsLimit(): void {
    $new = DrawdownFactory::create(['amount' => 10.1]);
    $this->fundingCase->setValues(['permissions' => ['drawdown_create']] + $this->fundingCase->toArray());

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($this->payoutProcess)
      ->willReturn(10.0);

    $result = $this->validator->validateNew($new, TRUE);
    static::assertFalse($result->isValid());
    static::assertEquals([
      'amount' => [
        EntityValidationError::new(
          'amount',
          'Requested amount is greater than available amount.'),
      ],
    ], $result->getErrors());
  }

}
