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

use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\Form\Handler\GetApplicationFormHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GetApplicationFormSubscriber implements EventSubscriberInterface {

  private GetApplicationFormHandlerInterface $formHandler;

  public function __construct(GetApplicationFormHandlerInterface $formHandler) {
    $this->formHandler = $formHandler;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      GetApplicationFormEvent::getEventName() => 'onGetForm',
      GetNewApplicationFormEvent::getEventName() => 'onGetNewForm',
    ];
  }

  public function onGetForm(GetApplicationFormEvent $event): void {
    if ($this->formHandler->supportsFundingCaseType($event->getFundingCaseType()->getName())) {
      $this->formHandler->handleGetForm($event);
    }
  }

  public function onGetNewForm(GetNewApplicationFormEvent $event): void {
    if ($this->formHandler->supportsFundingCaseType($event->getFundingCaseType()->getName())) {
      $this->formHandler->handleGetNewForm($event);
    }
  }

}
