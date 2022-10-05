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

namespace Civi\Funding\Form\Handler;

use Civi\Funding\Event\Remote\AbstractFundingGetFormEvent;
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingCase\GetNewApplicationFormEvent;
use Civi\Funding\Form\ApplicationFormFactoryInterface;
use Civi\Funding\Form\ApplicationFormInterface;

final class GetApplicationFormHandler implements GetApplicationFormHandlerInterface {

  private ApplicationFormFactoryInterface $formFactory;

  public function __construct(ApplicationFormFactoryInterface $formFactory) {
    $this->formFactory = $formFactory;
  }

  public function handleGetForm(GetApplicationFormEvent $event): void {
    $form = $this->formFactory->createFormOnGet($event);
    $this->mapFormToEvent($form, $event);
  }

  public function handleGetNewForm(GetNewApplicationFormEvent $event): void {
    $form = $this->formFactory->createNewFormOnGet($event);
    $this->mapFormToEvent($form, $event);
  }

  public function supportsFundingCaseType(string $fundingCaseType): bool {
    return $this->formFactory->supportsFundingCaseType($fundingCaseType);
  }

  private function mapFormToEvent(ApplicationFormInterface $form, AbstractFundingGetFormEvent $event): void {
    $event->setData($form->getData());
    $event->setJsonSchema($form->getJsonSchema());
    $event->setUiSchema($form->getUiSchema());
  }

}
