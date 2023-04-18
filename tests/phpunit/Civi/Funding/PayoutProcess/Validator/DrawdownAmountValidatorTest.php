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

use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\PayoutProcess\PayoutProcessManager;
use Civi\Funding\Validation\EntityValidationError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Validator\DrawdownAmountValidator
 */
final class DrawdownAmountValidatorTest extends TestCase {

  /**
   * @var \Civi\Funding\PayoutProcess\PayoutProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $payoutProcessManagerMock;

  private DrawdownAmountValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->payoutProcessManagerMock = $this->createMock(PayoutProcessManager::class);
    $this->validator = new DrawdownAmountValidator($this->payoutProcessManagerMock);
  }

  public function testGetEntityClass(): void {
    static::assertSame(DrawdownEntity::class, $this->validator::getEntityClass());
  }

  public function testValidateAmountUnchanged(): void {
    $current = DrawdownFactory::create(['status' => 'new']);
    $new = DrawdownFactory::create(['status' => 'accepted']);

    static::assertTrue($this->validator->validate($new, $current)->isValid());
  }

  public function testValidateAmountReduced(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => 9.9]);

    static::assertTrue($this->validator->validate($new, $current)->isValid());
  }

  public function testValidateAmountIncreased(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => 10.1]);

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('get')
      ->with($payoutProcess->getId())
      ->willReturn($payoutProcess);

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($payoutProcess)
      ->willReturn(0.1);

    static::assertTrue($this->validator->validate($new, $current)->isValid());
  }

  public function testValidateAmountExceedsLimit(): void {
    $current = DrawdownFactory::create(['amount' => 10.0]);
    $new = DrawdownFactory::create(['amount' => 10.1]);

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('get')
      ->with($payoutProcess->getId())
      ->willReturn($payoutProcess);

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($payoutProcess)
      ->willReturn(0.01);

    $result = $this->validator->validate($new, $current);
    static::assertFalse($result->isValid());
    static::assertEquals([
      'amount' => [
        EntityValidationError::new(
          'amount',
          'Requested amount is greater than available amount.'),
      ],
    ], $result->getErrors());
  }

  public function testValidateNew(): void {
    $new = DrawdownFactory::create(['amount' => 10.1]);

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('get')
      ->with($payoutProcess->getId())
      ->willReturn($payoutProcess);

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($payoutProcess)
      ->willReturn(10.1);

    static::assertTrue($this->validator->validateNew($new)->isValid());
  }

  public function testValidateNewExceedsLimit(): void {
    $new = DrawdownFactory::create(['amount' => 10.1]);

    $payoutProcess = PayoutProcessFactory::create();
    $this->payoutProcessManagerMock->method('get')
      ->with($payoutProcess->getId())
      ->willReturn($payoutProcess);

    $this->payoutProcessManagerMock->method('getAmountAvailable')
      ->with($payoutProcess)
      ->willReturn(10.0);

    $result = $this->validator->validateNew($new);
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
