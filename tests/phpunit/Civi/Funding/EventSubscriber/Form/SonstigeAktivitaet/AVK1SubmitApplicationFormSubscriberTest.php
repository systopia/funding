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
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitFormEvent;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
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
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1SubmitApplicationFormSubscriber
 */
final class AVK1SubmitApplicationFormSubscriberTest extends AbstractApplicationFormSubscriberTest {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  private AVK1SubmitApplicationFormSubscriber $subscriber;

  /**
   * @var \Civi\Funding\Form\Validation\FormValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $validatorMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $statusDeterminerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->validatorMock = $this->createMock(FormValidatorInterface::class);
    $this->statusDeterminerMock = $this->createMock(ApplicationProcessStatusDeterminer::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->subscriber = new AVK1SubmitApplicationFormSubscriber(
      $this->validatorMock,
      $this->statusDeterminerMock,
      $this->applicationProcessManagerMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      SubmitFormEvent::getEventName() => 'onSubmitForm',
    ];

    static::assertEquals($expectedSubscriptions, AVK1SubmitApplicationFormSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(AVK1SubmitApplicationFormSubscriber::class, $method));
    }
  }

  public function testOnSubmitForm(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);
    $applicationProcess = $event->getApplicationProcess();

    $validatedForm = new AVK1FormExisting(
      $event->getFundingProgram()['currency'],
      $applicationProcess->getId(),
      $event->getFundingCase()->getPermissions(),
      $applicationProcess->getRequestData(),
    );
    $postValidationData = [
      'action' => 'test',
      'titel' => 'Title2',
      'kurzbezeichnungDesInhalts' => 'Description2',
      'foo' => 'baz',
    ];

    $this->statusDeterminerMock->method('getStatus')->with($applicationProcess->getStatus(), 'test')
      ->willReturn('new_status');

    $this->validatorMock->expects(static::once())->method('validate')->with($validatedForm)->willReturn(
      new ValidationResult($postValidationData, new ErrorCollector())
    );

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with($event->getContactId(), $applicationProcess);

    $this->subscriber->onSubmitForm($event);

    static::assertSame('Title2', $applicationProcess->getTitle());
    static::assertSame('Description2', $applicationProcess->getShortDescription());
    static::assertSame($postValidationData, $applicationProcess->getRequestData());
    static::assertSame(SubmitFormEvent::ACTION_SHOW_FORM, $event->getAction());
    $expectedForm = new AVK1FormExisting(
      $event->getFundingProgram()['currency'],
      $applicationProcess->getId(),
      $event->getFundingCase()->getPermissions(),
      $postValidationData
    );
    static::assertEquals($expectedForm, $event->getForm());
  }

  public function testOnSubmitNewFormValidationFailed(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);
    $applicationProcess = $event->getApplicationProcess();

    $validatedForm = new AVK1FormExisting(
      $event->getFundingProgram()['currency'],
      $applicationProcess->getId(),
      $event->getFundingCase()->getPermissions(),
      $applicationProcess->getRequestData(),
    );
    $errorCollector = new ErrorCollector();
    $errorCollector->addError(
      new ValidationError(
        'keyword',
        new EmptySchema(new SchemaInfo(FALSE, NULL)),
        new DataInfo('bar', 'string', NULL, ['foo']),
        'Invalid value'
      )
    );
    $this->validatorMock->expects(static::once())->method('validate')->with($validatedForm)->willReturn(
      new ValidationResult(['foo' => 'baz'], $errorCollector)
    );

    $this->applicationProcessManagerMock->expects(static::never())->method('update');

    $this->subscriber->onSubmitForm($event);

    static::assertSame($data, $applicationProcess->getRequestData());
    static::assertSame(SubmitNewApplicationFormEvent::ACTION_SHOW_VALIDATION, $event->getAction());
    static::assertSame(['/foo' => ['Invalid value']], $event->getErrors());
  }

  public function testOnSubmitNewFormFundingCaseTypeNotMatch(): void {
    $event = $this->createEvent([], 'Foo');
    $this->validatorMock->expects(static::never())->method('validate');
    $this->subscriber->onSubmitForm($event);
    static::assertNull($event->getAction());
  }

  /**
   * @param array<string, mixed> $data
   * @param string $fundingCaseTypeName
   */
  private function createEvent(
    array $data,
    string $fundingCaseTypeName = 'AVK1SonstigeAktivitaet'
  ): SubmitFormEvent {
    return new SubmitFormEvent(RemoteFundingApplicationProcess::_getEntityName(), 'submitForm', [
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
