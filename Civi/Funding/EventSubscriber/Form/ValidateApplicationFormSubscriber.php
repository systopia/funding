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

namespace Civi\Funding\EventSubscriber\Form;

use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\ValidateNewApplicationFormEvent;
use Civi\Funding\Form\Handler\ValidateApplicationFormHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ValidateApplicationFormSubscriber implements EventSubscriberInterface {

  private ValidateApplicationFormHandlerInterface $formHandler;

  public function __construct(ValidateApplicationFormHandlerInterface $formHandler) {
    $this->formHandler = $formHandler;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ValidateApplicationFormEvent::getEventName() => 'onValidateForm',
      ValidateNewApplicationFormEvent::getEventName() => 'onValidateNewForm',
    ];
  }

  public function onValidateForm(ValidateApplicationFormEvent $event): void {
    if ($this->formHandler->supportsFundingCaseType($event->getFundingCaseType()->getName())) {
      $this->formHandler->handleValidateForm($event);
    }
  }

  public function onValidateNewForm(ValidateNewApplicationFormEvent $event): void {
    if ($this->formHandler->supportsFundingCaseType($event->getFundingCaseType()->getName())) {
      $this->formHandler->handleValidateNewForm($event);
    }
  }

}
