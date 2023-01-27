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
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitApplicationFormEvent;
use Webmozart\Assert\Assert;

/**
 * @method $this setData(array $data)
 */
final class SubmitFormAction extends AbstractFormAction {

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
    parent::__construct('submitForm', $applicationProcessBundleLoader, $eventDispatcher);
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();

    if (NULL === $event->getAction()) {
      throw new \API_Exception('Form not handled');
    }

    $result->rowCount = 1;
    $result->exchangeArray(['action' => $event->getAction()]);
    if (NULL !== $event->getMessage()) {
      $result['message'] = $event->getMessage();
    }

    switch ($event->getAction()) {
      case SubmitApplicationFormEvent::ACTION_SHOW_FORM:
        Assert::notNull($event->getForm());
        Assert::keyExists($event->getForm()->getData(), 'applicationProcessId');
        Assert::integer($event->getForm()->getData()['applicationProcessId']);
        $result['jsonSchema'] = $event->getForm()->getJsonSchema();
        $result['uiSchema'] = $event->getForm()->getUiSchema();
        $result['data'] = $event->getForm()->getData();
        break;

      case SubmitApplicationFormEvent::ACTION_SHOW_VALIDATION:
        Assert::notEmpty($event->getErrors());
        $result['errors'] = $event->getErrors();
        break;

      case SubmitApplicationFormEvent::ACTION_CLOSE_FORM:
        break;

      default:
        throw new \API_Exception(sprintf('Unknown action "%s"', $event->getAction()));
    }
  }

  public function getRequiredExtraParams(): array {
    return parent::getRequiredExtraParams() + ['contactId'];
  }

  /**
   * @throws \API_Exception
   */
  private function createEvent(): SubmitApplicationFormEvent {
    return SubmitApplicationFormEvent::fromApiRequest(
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
