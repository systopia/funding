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
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormBuilder;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Civi\Funding\FundingCase\FundingCaseManager;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AVK1SubmitNewApplicationFormSubscriber implements EventSubscriberInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private FundingCaseManager $fundingCaseManager;

  private ApplicationProcessStatusDeterminer $statusDeterminer;

  private FormValidatorInterface $validator;

  public function __construct(FormValidatorInterface $validator,
    ApplicationProcessStatusDeterminer $statusDeterminer,
    FundingCaseManager $fundingCaseManager,
    ApplicationProcessManager $applicationProcessManager
  ) {
    $this->validator = $validator;
    $this->statusDeterminer = $statusDeterminer;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->applicationProcessManager = $applicationProcessManager;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [SubmitNewApplicationFormEvent::getEventName() => 'onSubmitNewForm'];
  }

  public function onSubmitNewForm(SubmitNewApplicationFormEvent $event): void {
    if ('AVK1SonstigeAktivitaet' !== $event->getFundingCaseType()->getName()) {
      return;
    }

    $form = AVK1FormBuilder::new()
      ->isNew(TRUE)
      ->fundingProgram($event->getFundingProgram())
      ->fundingCaseType($event->getFundingCaseType())
      ->data($event->getData())
      ->build();
    $validationResult = $this->validator->validate($form);

    /** @phpstan-var array<string, mixed>&array{
     *   action: string,
     *   titel: string,
     *   kurzbezeichnungDesInhalts: string,
     * } $data
     */
    $data = $validationResult->getData();

    if ($validationResult->isValid()) {
      $fundingCase = $this->fundingCaseManager->create($event->getContactId(), [
        'funding_program' => $event->getFundingProgram(),
        'funding_case_type' => $event->getFundingCaseType(),
        // TODO: This has to be part of the form or determined somehow else.
        'recipient_contact_id' => $event->getContactId(),
      ]);

      $applicationProcess = $this->applicationProcessManager->create($event->getContactId(), [
        'funding_case' => $fundingCase,
        'status' => $this->statusDeterminer->getStatusForNew($data['action']),
        'title' => $data['titel'],
        'short_description' => $data['kurzbezeichnungDesInhalts'],
        'request_data' => $data,
      ]);

      // TODO: Change message
      $event->setMessage(E::ts('Success! (Application process ID: %1)',
        ['%1' => $applicationProcess->getId()]));
      $event->setForm(AVK1FormBuilder::new()
        ->fundingProgram($event->getFundingProgram())
        ->fundingCase($fundingCase)
        ->applicationProcess($applicationProcess)
        ->data($applicationProcess->getRequestData())
        ->build()
      );
    }
    else {
      // TODO: Change message
      $event->setMessage(E::ts('Validation failed'));
      foreach ($validationResult->getLeafErrorMessages() as $jsonPointer => $messages) {
        $event->addErrorsAt($jsonPointer, $messages);
      }
    }
  }

}
