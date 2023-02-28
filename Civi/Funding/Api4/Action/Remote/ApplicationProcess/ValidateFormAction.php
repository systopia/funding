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
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\Event\Remote\ApplicationProcess\ValidateApplicationFormEvent;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\Funding\Exception\FundingException;
use Webmozart\Assert\Assert;

/**
 * @method $this setData(array $data)
 */
final class ValidateFormAction extends AbstractFormAction {

  /**
   * @var array
   * @phpstan-var array<string, mixed>
   * @required
   */
  protected ?array $data = NULL;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    CiviEventDispatcherInterface $eventDispatcher
  ) {
    parent::__construct('validateForm', $applicationProcessBundleLoader, $eventDispatcher);
    $this->_eventDispatcher = $eventDispatcher;
    $this->_authorizeRequestEventName = FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REQUEST_INIT_EVENT_NAME;
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

    if (NULL === $event->isValid()) {
      throw new FundingException('Form not validated');
    }

    $result->rowCount = 1;
    $result->exchangeArray([
      'valid' => $event->isValid(),
      'errors' => $event->getErrors(),
    ]);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createEvent(): ValidateApplicationFormEvent {
    return ValidateApplicationFormEvent::fromApiRequest(
      $this,
      $this->createEventParams($this->getApplicationProcessId())
    );
  }

  public function getApplicationProcessId(): int {
    Assert::notNull($this->data);
    Assert::keyExists($this->data, 'applicationProcessId');
    Assert::integer($this->data['applicationProcessId']);

    return $this->data['applicationProcessId'];
  }

}
