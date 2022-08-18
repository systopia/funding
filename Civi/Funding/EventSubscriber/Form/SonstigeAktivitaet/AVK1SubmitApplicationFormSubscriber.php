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
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitFormEvent;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormExisting;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use CRM_Funding_ExtensionUtil as E;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AVK1SubmitApplicationFormSubscriber implements EventSubscriberInterface {

  private ApplicationProcessManager $applicationProcessManager;

  private ApplicationProcessStatusDeterminer $statusDeterminer;

  private FormValidatorInterface $validator;

  public function __construct(FormValidatorInterface $validator,
    ApplicationProcessStatusDeterminer $statusDeterminer,
    ApplicationProcessManager $applicationProcessManager
  ) {
    $this->validator = $validator;
    $this->statusDeterminer = $statusDeterminer;
    $this->applicationProcessManager = $applicationProcessManager;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [SubmitFormEvent::getEventName() => 'onSubmitForm'];
  }

  public function onSubmitForm(SubmitFormEvent $event): void {
    if ('AVK1SonstigeAktivitaet' !== $event->getFundingCaseType()['name']) {
      return;
    }

    $form = new AVK1FormExisting(
      $event->getFundingProgram()->getCurrency(),
      $event->getApplicationProcess()->getId(),
      $event->getFundingCase()->getPermissions(),
      $event->getData()
    );
    $validationResult = $this->validator->validate($form);

    /** @phpstan-var array<string, mixed>&array{
     *   action: string,
     *   titel: string,
     *   kurzbezeichnungDesInhalts: string,
     * } $data
     */
    $data = $validationResult->getData();

    if ($validationResult->isValid()) {
      $applicationProcess = $event->getApplicationProcess();
      $applicationProcess->setStatus(
        $this->statusDeterminer->getStatus($applicationProcess->getStatus(), $data['action'])
      );
      $applicationProcess->setRequestData($data);
      $applicationProcess->setTitle($data['titel']);
      $applicationProcess->setShortDescription($data['kurzbezeichnungDesInhalts']);
      $this->applicationProcessManager->update($event->getContactId(), $applicationProcess, $event->getFundingCase());

      // TODO: Change message
      $event->setMessage(E::ts('Success!'));
      $event->setForm(new AVK1FormExisting(
        $event->getFundingProgram()->getCurrency(),
        $applicationProcess->getId(),
        $event->getFundingCase()->getPermissions(),
        $validationResult->getData()
      ));
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
