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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Form\ApplicationValidationResult;
use Civi\Funding\Form\ApplicationValidatorInterface;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandler
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand
 * @covers \Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateResult
 */
final class ApplicationFormValidateHandlerTest extends TestCase {

  private ApplicationFormValidateHandler $handler;

  /**
   * @var \Civi\Funding\Form\ApplicationValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->validatorMock = $this->createMock(ApplicationValidatorInterface::class);
    $this->handler = new ApplicationFormValidateHandler(
      $this->validatorMock
    );
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle();
    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];

    $data = ['foo' => 'bar'];
    $validatedData = new ValidatedApplicationDataMock();
    $errorMessages = ['/a/b' => ['error']];
    $validationResult = ApplicationValidationResult::newInvalid($errorMessages, $validatedData);

    $this->validatorMock->expects(static::once())->method('validateExisting')
      ->with($applicationProcessBundle, $statusList, $data)
      ->willReturn($validationResult);

    $command = new ApplicationFormValidateCommand($applicationProcessBundle, $statusList, $data);
    $result = $this->handler->handle($command);
    static::assertSame($validatedData->getRawData(), $result->getData());
    static::assertSame($validationResult->getErrorMessages(), $result->getErrors());
    static::assertFalse($validationResult->isValid());
  }

}
