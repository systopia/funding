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

use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormNew;
use Civi\Funding\Form\Validation\ValidationResult;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1ValidateNewApplicationFormSubscriber
 */
final class AVK1ValidateNewApplicationFormSubscriberTest extends AbstractNewApplicationFormSubscriberTest {

  private AVK1ValidateNewApplicationFormSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->subscriber = new AVK1ValidateNewApplicationFormSubscriber($this->validatorMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ValidateNewApplicationFormEvent::getEventName() => 'onValidateNewForm',
    ];

    static::assertEquals($expectedSubscriptions, AVK1ValidateNewApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(AVK1ValidateNewApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnValidateNewFormValid(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);

    $validatedForm = new AVK1FormNew(
      $event->getFundingProgram()->getCurrency(),
      $event->getFundingCaseType()->getId(),
      $event->getFundingProgram()->getId(),
      $event->getFundingProgram()->getPermissions(),
      $data
    );
    $postValidationData = ['foo' => 'baz'];
    $this->validatorMock->expects(static::once())->method('validate')
      ->with($validatedForm)
      ->willReturn(new ValidationResult($postValidationData, new ErrorCollector()));

    $this->subscriber->onValidateNewForm($event);

    static::assertTrue($event->isValid());
    static::assertSame([], $event->getErrors());
  }

  public function testOnValidateNewFormValidationFailed(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);

    $validatedForm = new AVK1FormNew(
      $event->getFundingProgram()->getCurrency(),
      $event->getFundingCaseType()->getId(),
      $event->getFundingProgram()->getId(),
      $event->getFundingProgram()->getPermissions(),
      $data
    );
    $errorCollector = new ErrorCollector();
    $errorCollector->addError(new ValidationError(
      'keyword',
      new EmptySchema(new SchemaInfo(FALSE, NULL)),
      new DataInfo('bar', 'string', NULL, ['foo']),
      'Invalid value'));
    $this->validatorMock->expects(static::once())->method('validate')
      ->with($validatedForm)
      ->willReturn(new ValidationResult(['foo' => 'baz'], $errorCollector));

    $this->subscriber->onValidateNewForm($event);

    static::assertFalse($event->isValid());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
  }

  public function testOnValidateNewFormFundingCaseTypeNotMatch(): void {
    $event = $this->createEvent([], 'Foo');
    $this->validatorMock->expects(static::never())->method('validate');
    $this->subscriber->onValidateNewForm($event);
    static::assertNull($event->isValid());
  }

  /**
   * @param array<string, mixed> $data
   * @param string $fundingCaseTypeName
   */
  private function createEvent(array $data,
    string $fundingCaseTypeName = 'AVK1SonstigeAktivitaet'
  ): ValidateNewApplicationFormEvent {
    return new ValidateNewApplicationFormEvent('RemoteFundingCase', 'validateNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => $this->createFundingProgram(),
      'fundingCaseType' => $this->createFundingCaseType($fundingCaseTypeName),
      'data' => $data,
    ]);
  }

}
