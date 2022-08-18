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

use Civi\Funding\Event\Remote\ApplicationProcess\GetFormEvent;
use Civi\Funding\Form\SonstigeAktivitaet\AVK1FormExisting;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AVK1GetApplicationFormSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [GetFormEvent::getEventName() => 'onGetForm'];
  }

  public function onGetForm(GetFormEvent $event): void {
    if ('AVK1SonstigeAktivitaet' !== $event->getFundingCaseType()->getName()) {
      return;
    }

    $form = new AVK1FormExisting(
      $event->getFundingProgram()->getCurrency(),
      $event->getApplicationProcess()->getId(),
      $event->getFundingCase()->getPermissions(),
      $event->getApplicationProcess()->getRequestData(),
    );

    $event->setData($form->getData());
    $event->setJsonSchema($form->getJsonSchema());
    $event->setUiSchema($form->getUiSchema());
  }

}
