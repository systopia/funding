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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AVK1SubmitNewApplicationFormSubscriber implements EventSubscriberInterface {

  private FormValidatorInterface $validator;

  public function __construct(FormValidatorInterface $validator) {
    $this->validator = $validator;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [SubmitNewApplicationFormEvent::getEventName() => 'onSubmitNewForm'];
  }

  public function onSubmitNewForm(SubmitNewApplicationFormEvent $event): void {
    if ('AVK1SonstigeAktivitaet' !== $event->getFundingCaseType()['name']) {
      return;
    }

    $form = new AVK1FormNew(
      $event->getFundingProgram()['currency'],
      $event->getFundingCaseType()['id'],
      $event->getFundingProgram()['id'],
      $event->getFundingProgram()['permissions'],
      $event->getData()
    );
    $validationResult = $this->validator->validate($form);

    if ($validationResult->isValid()) {
      // TODO: Change and translate message, create new funding case, ...
      $event->setMessage('Success!');
      $event->setForm(new AVK1FormNew(
        $event->getFundingProgram()['currency'],
        $event->getFundingCaseType()['id'],
        $event->getFundingProgram()['id'],
        $event->getFundingProgram()['permissions'],
        $validationResult->getData()
      ));
    }
    else {
      foreach ($validationResult->getLeafErrorMessages() as $jsonPointer => $messages) {
        // TODO: Change and translate message
        $event->setMessage('Validation failed');
        $event->addErrorsAt($jsonPointer, $messages);
      }
    }
  }

}
