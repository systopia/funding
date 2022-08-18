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

use Civi\Funding\Event\Remote\ApplicationProcess\ValidateFormEvent;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormExisting;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AVK1ValidateApplicationFormSubscriber implements EventSubscriberInterface {

  private FormValidatorInterface $validator;

  public function __construct(FormValidatorInterface $validator) {
    $this->validator = $validator;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ValidateFormEvent::getEventName() => 'onValidateForm'];
  }

  public function onValidateForm(ValidateFormEvent $event): void {
    if ('AVK1SonstigeAktivitaet' !== $event->getFundingCaseType()['name']) {
      return;
    }

    $form = new AVK1FormExisting(
      $event->getFundingProgram()['currency'],
      $event->getApplicationProcess()->getId(),
      $event->getFundingCase()->getPermissions(),
      $event->getData()
    );
    $validationResult = $this->validator->validate($form);

    if ($validationResult->isValid()) {
      $event->setValid(TRUE);
    }
    else {
      foreach ($validationResult->getLeafErrorMessages() as $jsonPointer => $messages) {
        // TODO: Change and translate message
        $event->addErrorsAt($jsonPointer, $messages);
      }
    }
  }

}
