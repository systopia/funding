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

use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormNew;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\Form\Validation\ValidationResult;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1SubmitNewApplicationFormSubscriber
 */
final class AVK1SubmitNewApplicationFormSubscriberTest extends TestCase {

  private AVK1SubmitNewApplicationFormSubscriber $subscriber;

  /**
   * @var \Civi\Funding\Form\Validation\FormValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->validatorMock = $this->createMock(FormValidatorInterface::class);
    $this->subscriber = new AVK1SubmitNewApplicationFormSubscriber($this->validatorMock);
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      SubmitNewApplicationFormEvent::getEventName() => 'onSubmitNewForm',
    ];

    static::assertEquals($expectedSubscriptions, AVK1SubmitNewApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(AVK1SubmitNewApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnSubmitNewFormSuccess(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);

    $validatedForm = new AVK1FormNew(
      $event->getFundingProgram()['currency'],
      $event->getFundingCaseType()['id'],
      $event->getFundingProgram()['id'],
      $event->getFundingProgram()['permissions'],
      $data
    );
    $postValidationData = ['foo' => 'baz'];
    $this->validatorMock->expects(static::once())->method('validate')
      ->with($validatedForm)
      ->willReturn(new ValidationResult($postValidationData, new ErrorCollector()));

    $this->subscriber->onSubmitNewForm($event);

    static::assertSame(SubmitNewApplicationFormEvent::ACTION_SHOW_FORM, $event->getAction());
    $expectedForm = new AVK1FormNew(
      $event->getFundingProgram()['currency'],
      $event->getFundingCaseType()['id'],
      $event->getFundingProgram()['id'],
      $event->getFundingProgram()['permissions'],
      $postValidationData
    );
    static::assertEquals($expectedForm, $event->getForm());
  }

  public function testOnSubmitNewFormValidationFailed(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);

    $validatedForm = new AVK1FormNew(
      $event->getFundingProgram()['currency'],
      $event->getFundingCaseType()['id'],
      $event->getFundingProgram()['id'],
      $event->getFundingProgram()['permissions'],
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

    $this->subscriber->onSubmitNewForm($event);

    static::assertSame(SubmitNewApplicationFormEvent::ACTION_SHOW_VALIDATION, $event->getAction());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
  }

  public function testOnSubmitNewFormFundingCaseTypeNotMatch(): void {
    $event = $this->createEvent([], 'Foo');
    $this->validatorMock->expects(static::never())->method('validate');
    $this->subscriber->onSubmitNewForm($event);
    static::assertNull($event->getAction());
  }

  /**
   * @param array<string, mixed> $data
   * @param string $fundingCaseTypeName
   */
  private function createEvent(array $data,
    string $fundingCaseTypeName = 'AVK1SonstigeAktivitaet'
  ): SubmitNewApplicationFormEvent {
    return new SubmitNewApplicationFormEvent('RemoteFundingCase', 'GetNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => ['id' => 2, 'currency' => '€', 'permissions' => []],
      'fundingCaseType' => ['id' => 3, 'name' => $fundingCaseTypeName],
      'data' => $data,
    ]);
  }

}
