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

namespace Civi\Funding\Validation;

use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Validation\EntityValidator
 */
final class EntityValidatorTest extends TestCase {

  /**
   * @var \Civi\Funding\Validation\EntityValidatorLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorLoaderMock;

  private EntityValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->validatorLoaderMock = $this->createMock(EntityValidatorLoader::class);
    $this->validator = new EntityValidator($this->validatorLoaderMock);
  }

  public function testValidate(): void {
    $validatorMock1 = $this->createMock(EntityValidatorInterface::class);
    $validatorMock2 = $this->createMock(EntityValidatorInterface::class);
    $this->validatorLoaderMock->method('getValidators')
      ->with(FundingProgramEntity::class)
      ->willReturn([$validatorMock1, $validatorMock2]);

    $new = FundingProgramFactory::createFundingProgram(['title' => 'New']);
    $current = FundingProgramFactory::createFundingProgram(['title' => 'Current']);

    $error1 = EntityValidationError::new('test1', 'Foo1');
    $validatorMock1->method('validate')->with($new, $current)->willReturn(EntityValidationResult::new($error1));

    $error2 = EntityValidationError::new('test2', 'Foo2');
    $validatorMock2->method('validate')->with($new, $current)->willReturn(EntityValidationResult::new($error2));

    $result = $this->validator->validate($new, $current);
    static::assertSame(['test1' => [$error1], 'test2' => [$error2]], $result->getErrors());
  }

  public function testValidateNew(): void {
    $validatorMock1 = $this->createMock(EntityValidatorInterface::class);
    $validatorMock2 = $this->createMock(EntityValidatorInterface::class);
    $this->validatorLoaderMock->method('getValidators')
      ->with(FundingProgramEntity::class)
      ->willReturn([$validatorMock1, $validatorMock2]);

    $new = FundingProgramFactory::createFundingProgram(['title' => 'New']);

    $error1 = EntityValidationError::new('test1', 'Foo1');
    $validatorMock1->method('validateNew')->with($new)->willReturn(EntityValidationResult::new($error1));

    $error2 = EntityValidationError::new('test2', 'Foo2');
    $validatorMock2->method('validateNew')->with($new)->willReturn(EntityValidationResult::new($error2));

    $result = $this->validator->validateNew($new);
    static::assertSame(['test1' => [$error1], 'test2' => [$error2]], $result->getErrors());
  }

}
