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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Traits\ApplicationProcessIdParameterTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\Event\Remote\ApplicationProcess\GetApplicationFormEvent;
use Civi\Funding\Exception\FundingException;
use CRM_Funding_ExtensionUtil as E;

class GetFormAction extends AbstractFormAction {

  use ApplicationProcessIdParameterTrait;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    CiviEventDispatcherInterface $eventDispatcher
  ) {
    parent::__construct('getForm', $applicationProcessBundleLoader, $eventDispatcher);
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();
    if (NULL === $event->getJsonSchema() || NULL === $event->getUiSchema()) {
      throw new FundingException(
        E::ts('Application process with ID "%1" not found', [1 => $this->applicationProcessId]),
        'invalid_arguments'
      );
    }

    $result->rowCount = 1;
    $result->exchangeArray([
      'jsonSchema' => $event->getJsonSchema(),
      'uiSchema' => $event->getUiSchema(),
      'data' => $event->getData(),
    ]);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createEvent(): GetApplicationFormEvent {
    return GetApplicationFormEvent::fromApiRequest($this, $this->createEventParams($this->getApplicationProcessId()));
  }

}
