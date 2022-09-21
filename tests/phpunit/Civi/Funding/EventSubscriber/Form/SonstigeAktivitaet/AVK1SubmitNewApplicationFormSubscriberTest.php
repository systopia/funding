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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormBuilder;
use Civi\Funding\Form\Validation\ValidationResult;
use Civi\Funding\FundingCase\FundingCaseManager;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\DataInfo;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Schemas\EmptySchema;
use PHPUnit\Framework\MockObject\MockObject;
use Systopia\JsonSchema\Errors\ErrorCollector;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1SubmitNewApplicationFormSubscriber
 */
final class AVK1SubmitNewApplicationFormSubscriberTest extends AbstractNewApplicationFormSubscriberTest {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationProcessManagerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessStatusDeterminer&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $statusDeterminerMock;

  private AVK1SubmitNewApplicationFormSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->statusDeterminerMock = $this->createMock(ApplicationProcessStatusDeterminer::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->subscriber = new AVK1SubmitNewApplicationFormSubscriber(
      $this->validatorMock,
      $this->statusDeterminerMock,
      $this->fundingCaseManagerMock,
      $this->applicationProcessManagerMock,
    );
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

    $validatedForm = AVK1FormBuilder::new()
      ->isNew(TRUE)
      ->fundingProgram($event->getFundingProgram())
      ->fundingCaseType($event->getFundingCaseType())
      ->data($data)
      ->build();
    ;
    $postValidationData = [
      'action' => 'test',
      'titel' => 'Title',
      'kurzbezeichnungDesInhalts' => 'Description',
      'foo' => 'baz',
      'finanzierung' => ['beantragterZuschuss' => 1.2],
    ];

    $this->statusDeterminerMock->method('getStatusForNew')->with('test')->willReturn('new_status');

    $this->validatorMock->expects(static::once())->method('validate')->with($validatedForm)->willReturn(
      new ValidationResult($postValidationData, new ErrorCollector())
    );

    $fundingCase = FundingCaseEntity::fromArray([
      'id' => 4,
      'funding_program_id' => $event->getFundingProgram()->getId(),
      'funding_case_type_id' => $event->getFundingCaseType()->getId(),
      'status' => 'open',
      // TODO: This has to be adapted when fixed in the CUT.
      'recipient_contact_id' => $event->getContactId(),
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'permissions' => ['test_permission'],
    ]);
    $this->fundingCaseManagerMock->expects(static::once())->method('create')
      ->with(
        $event->getContactId(), [
          'funding_program' => $event->getFundingProgram(),
          'funding_case_type' => $event->getFundingCaseType(),
          // TODO: This has to be adapted when fixed in the CUT.
          'recipient_contact_id' => $event->getContactId(),
        ])->willReturn($fundingCase);

    $applicationProcess = ApplicationProcessEntity::fromArray([
      'id' => 5,
      'funding_case_id' => $fundingCase->getId(),
      'status' => 'new_status',
      'title' => 'Title',
      'short_description' => 'Description',
      'request_data' => $postValidationData,
      'amount_requested' => 1.2,
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'start_date' => NULL,
      'end_date' => NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'is_review_calculative' => NULL,
    ]);
    $this->applicationProcessManagerMock->expects(static::once())->method('create')
      ->with($event->getContactId(), [
        'funding_case' => $fundingCase,
        'status' => 'new_status',
        'title' => 'Title',
        'short_description' => 'Description',
        'request_data' => $postValidationData,
        'amount_requested' => 1.2,
      ])->willReturn($applicationProcess);

    $this->subscriber->onSubmitNewForm($event);

    static::assertSame(SubmitNewApplicationFormEvent::ACTION_SHOW_FORM, $event->getAction());
    $expectedForm = AVK1FormBuilder::new()
      ->fundingProgram($event->getFundingProgram())
      ->fundingCase($fundingCase)
      ->applicationProcess($applicationProcess)
      ->data($postValidationData)
      ->build();
    static::assertEquals($expectedForm, $event->getForm());
  }

  public function testOnSubmitNewFormValidationFailed(): void {
    $data = ['foo' => 'bar'];
    $event = $this->createEvent($data);

    $validatedForm = AVK1FormBuilder::new()
      ->isNew(TRUE)
      ->fundingProgram($event->getFundingProgram())
      ->fundingCaseType($event->getFundingCaseType())
      ->data($data)
      ->build();
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

    $this->fundingCaseManagerMock->expects(static::never())->method('create');

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
  private function createEvent(
    array $data,
    string $fundingCaseTypeName = 'AVK1SonstigeAktivitaet'
  ): SubmitNewApplicationFormEvent {
    return new SubmitNewApplicationFormEvent('RemoteFundingCase', 'submitNewApplicationForm', [
      'remoteContactId' => '00',
      'contactId' => 1,
      'fundingProgram' => $this->createFundingProgram(),
      'fundingCaseType' => $this->createFundingCaseType($fundingCaseTypeName),
      'data' => $data,
    ]);
  }

}
