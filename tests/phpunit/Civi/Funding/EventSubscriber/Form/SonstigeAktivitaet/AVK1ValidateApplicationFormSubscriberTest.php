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

namespace Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet;

use Civi\Api4\RemoteFundingApplicationProcess;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateFormEvent;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormExisting;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use PHPUnit\Framework\MockObject\MockObject;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1ValidateApplicationFormSubscriber
 */
final class AVK1ValidateApplicationFormSubscriberTest extends AbstractApplicationFormSubscriberTest {

  private AVK1ValidateApplicationFormSubscriber $subscriber;

  /**
   * @var \Civi\Funding\Form\Validation\FormValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->validatorMock = $this->createMock(FormValidatorInterface::class);
    $this->subscriber = new AVK1ValidateApplicationFormSubscriber($this->validatorMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ValidateFormEvent::getEventName() => 'onValidateForm',
    ];

    static::assertEquals($expectedSubscriptions, AVK1ValidateApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(AVK1ValidateApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnValidateFormValid(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);
    $applicationProcess = $event->getApplicationProcess();

    $validatedForm = new AVK1FormExisting(
      $event->getFundingProgram()['currency'],
      $applicationProcess->getId(),
      $event->getFundingCase()->getPermissions(),
      $applicationProcess->getRequestData(),
    );
    $postValidationData = ['foo' => 'baz'];

    $this->validatorMock->expects(static::once())->method('validate')->with($validatedForm)->willReturn(
      new ValidationResult($postValidationData, new ErrorCollector())
    );

    $this->subscriber->onValidateForm($event);

    static::assertTrue($event->isValid());
    static::assertSame([], $event->getErrors());
  }

  public function testOnValidateInvalid(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);
    $applicationProcess = $event->getApplicationProcess();

    $validatedForm = new AVK1FormExisting(
      $event->getFundingProgram()['currency'],
      $applicationProcess->getId(),
      $event->getFundingCase()->getPermissions(),
      $applicationProcess->getRequestData(),
    );
    $postValidationData = ['foo' => 'baz'];

    $errorCollector = new ErrorCollector();
    $errorCollector->addError(new ValidationError(
      'keyword',
      new EmptySchema(new SchemaInfo(FALSE, NULL)),
      new DataInfo('bar', 'string', NULL, ['foo']),
      'Invalid value'));
    $this->validatorMock->expects(static::once())->method('validate')
      ->with($validatedForm)
      ->willReturn(new ValidationResult($postValidationData, $errorCollector));

    $this->subscriber->onValidateForm($event);

    static::assertFalse($event->isValid());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
  }

  public function testOnValidateFormFundingCaseTypeNotMatch(): void {
    $event = $this->createEvent([], 'Foo');
    $this->validatorMock->expects(static::never())->method('validate');
    $this->subscriber->onValidateForm($event);
    static::assertNull($event->isValid());
  }

  /**
   * @param array<string, mixed> $data
   * @param string $fundingCaseTypeName
   */
  private function createEvent(
    array $data,
    string $fundingCaseTypeName = 'AVK1SonstigeAktivitaet'
  ): ValidateFormEvent {
    return new ValidateFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'validateForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'applicationProcess' => $this->createApplicationProcess(),
      'fundingCase' => $this->createFundingCase(),
      'fundingProgram' => $this->createFundingProgram(),
      'fundingCaseType' => $this->createFundingCaseType($fundingCaseTypeName),
      'data' => $data,
    ]);
  }

}
