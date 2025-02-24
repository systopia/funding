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

namespace Civi\Funding\ApplicationProcess\Validator;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Validator\IsReviewContentValidator
 */
final class IsReviewContentValidatorTest extends TestCase {

  private IsReviewContentValidator $validator;

  private FundingCaseEntity $fundingCase;

  protected function setUp(): void {
    parent::setUp();
    $fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->validator = new IsReviewContentValidator($fundingCaseManagerMock);
    $this->fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseManagerMock->method('get')->with(FundingCaseFactory::DEFAULT_ID)->willReturn($this->fundingCase);
  }

  public function testGetEntityClass(): void {
    static::assertSame(ApplicationProcessEntity::class, $this->validator::getEntityClass());
  }

  public function testValidateUnchanged(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'is_review_content' => TRUE,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'is_review_content' => TRUE,
    ]);

    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateWithoutPermission(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'is_review_content' => NULL,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'is_review_content' => TRUE,
    ]);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to change content review result is missing.');
    $this->validator->validate($new, $current, TRUE)->isValid();
  }

  public function testValidateWithPermission(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'is_review_content' => NULL,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'is_review_content' => TRUE,
    ]);

    $this->fundingCase->setValues(['permissions' => ['review_content']] + $this->fundingCase->toArray());
    static::assertTrue($this->validator->validate($new, $current, TRUE)->isValid());
  }

  public function testValidateNewNotSet(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'id' => NULL,
      'title' => 'new',
      'is_review_content' => NULL,
    ]);

    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateNewWithoutPermission(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'id' => NULL,
      'title' => 'new',
      'is_review_content' => TRUE,
    ]);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to change content review result is missing.');
    $this->validator->validateNew($new, TRUE)->isValid();
  }

  public function testValidateNewWithPermission(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'id' => NULL,
      'title' => 'new',
      'is_review_content' => TRUE,
    ]);

    $this->fundingCase->setValues(['permissions' => ['review_content']] + $this->fundingCase->toArray());
    static::assertTrue($this->validator->validateNew($new, TRUE)->isValid());
  }

  public function testValidateResetWithoutPermissionCheckIsValid(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'is_review_content' => NULL,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'is_review_content' => TRUE,
    ]);

    $this->fundingCase->setValues(['permissions' => ['test']] + $this->fundingCase->toArray());
    static::assertTrue($this->validator->validate($new, $current, FALSE)->isValid());
  }

  public function testValidateChangeWithoutPermissionCheckIsInvalid(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'is_review_content' => TRUE,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'is_review_content' => NULL,
    ]);

    $this->fundingCase->setValues(['permissions' => ['test']] + $this->fundingCase->toArray());

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to change content review result is missing.');
    static::assertFalse($this->validator->validate($new, $current, FALSE)->isValid());
  }

}
