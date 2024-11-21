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
use Civi\Funding\Permission\FundingCase\FundingCaseContactsLoaderInterface;
use Civi\Funding\Validation\EntityValidationError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Validator\ReviewerContentContactValidator
 */
final class ReviewerContentContactValidatorTest extends TestCase {

  private FundingCaseEntity $fundingCase;

  /**
   * @var \Civi\Funding\Permission\FundingCase\FundingCaseContactsLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseContactsLoaderMock;

  private ReviewerContentContactValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->fundingCaseContactsLoaderMock = $this->createMock(FundingCaseContactsLoaderInterface::class);
    $fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->validator = new ReviewerContentContactValidator(
      $this->fundingCaseContactsLoaderMock,
      $fundingCaseManagerMock,
    );
    $this->fundingCase = FundingCaseFactory::createFundingCase();
    $fundingCaseManagerMock->method('get')->with(FundingCaseFactory::DEFAULT_ID)->willReturn($this->fundingCase);
  }

  public function testGetEntityClass(): void {
    static::assertSame(ApplicationProcessEntity::class, $this->validator::getEntityClass());
  }

  public function testValidateUnchanged(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'reviewer_cont_contact_id' => 1,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'reviewer_cont_contact_id' => 1,
    ]);

    static::assertTrue($this->validator->validate($new, $current)->isValid());
  }

  public function testValidateWithoutPermission(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'reviewer_cont_contact_id' => 1,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'reviewer_cont_contact_id' => 2,
    ]);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to change content reviewer is missing.');
    static::assertTrue($this->validator->validate($new, $current)->isValid());
  }

  public function testValidateWithPermission(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'reviewer_cont_contact_id' => 2,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'reviewer_cont_contact_id' => 1,
    ]);

    $this->fundingCaseContactsLoaderMock->method('getContactsWithAnyPermission')
      ->with($this->fundingCase, ['review_content'])
      ->willReturn([2 => 'foo', 3 => 'bar']);
    $this->fundingCase->setValues(['permissions' => ['review_content']] + $this->fundingCase->toArray());
    static::assertTrue($this->validator->validate($new, $current)->isValid());
  }

  public function testValidateContactNotAllowed(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'new',
      'reviewer_cont_contact_id' => 2,
    ]);
    $current = ApplicationProcessFactory::createApplicationProcess([
      'title' => 'current',
      'reviewer_cont_contact_id' => 1,
    ]);

    $this->fundingCaseContactsLoaderMock->method('getContactsWithAnyPermission')
      ->with($this->fundingCase, ['review_content'])
      ->willReturn([3 => 'bar']);
    $this->fundingCase->setValues(['permissions' => ['review_content']] + $this->fundingCase->toArray());
    $result = $this->validator->validate($new, $current);
    static::assertFalse($result->isValid());
    static::assertEquals([
      'reviewer_cont_contact_id' => [
        EntityValidationError::new(
        'reviewer_cont_contact_id',
        'Contact 2 is not allowed as content reviewer.'),
      ],
    ], $result->getErrors());
  }

  public function testValidateNewNotSet(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'id' => NULL,
      'title' => 'new',
      'reviewer_cont_contact_id' => NULL,
    ]);

    static::assertTrue($this->validator->validateNew($new)->isValid());
  }

  public function testValidateNewWithoutPermission(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'id' => NULL,
      'title' => 'new',
      'reviewer_cont_contact_id' => 1,
    ]);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to change content reviewer is missing.');
    static::assertTrue($this->validator->validateNew($new)->isValid());
  }

  public function testValidateNewWithPermission(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'id' => NULL,
      'title' => 'new',
      'reviewer_cont_contact_id' => 1,
    ]);

    $this->fundingCaseContactsLoaderMock->method('getContactsWithAnyPermission')
      ->with($this->fundingCase, ['review_content'])
      ->willReturn([1 => 'foo']);
    $this->fundingCase->setValues(['permissions' => ['review_content']] + $this->fundingCase->toArray());
    static::assertTrue($this->validator->validateNew($new)->isValid());
  }

  public function testValidateNewContactNotAllowed(): void {
    $new = ApplicationProcessFactory::createApplicationProcess([
      'id' => NULL,
      'title' => 'new',
      'reviewer_cont_contact_id' => 1,
    ]);

    $this->fundingCaseContactsLoaderMock->method('getContactsWithAnyPermission')
      ->with($this->fundingCase, ['review_content'])
      ->willReturn([2 => 'foo']);
    $this->fundingCase->setValues(['permissions' => ['review_content']] + $this->fundingCase->toArray());
    $result = $this->validator->validateNew($new);
    static::assertFalse($result->isValid());
    static::assertEquals([
      'reviewer_cont_contact_id' => [
        EntityValidationError::new(
        'reviewer_cont_contact_id',
        'Contact 1 is not allowed as content reviewer.'),
      ],
    ], $result->getErrors());
  }

}
