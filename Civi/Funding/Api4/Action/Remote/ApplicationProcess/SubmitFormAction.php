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
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Remote\AbstractRemoteFundingAction;
use Civi\Funding\Api4\Action\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\Remote\ApplicationProcess\SubmitFormEvent;
use Civi\Funding\Event\Remote\FundingEvents;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * @method void setData(array $data)
 */
final class SubmitFormAction extends AbstractRemoteFundingAction {

  use RemoteFundingActionContactIdRequiredTrait;

  /**
   * @var array<string, mixed>
   * @required
   */
  protected array $data;

  private RemoteFundingEntityManagerInterface $_remoteFundingEntityManager;

  public function __construct(
    RemoteFundingEntityManagerInterface $remoteFundingEntityManager,
    CiviEventDispatcher $eventDispatcher
  ) {
    parent::__construct('RemoteFundingApplicationProcess', 'submitForm');
    $this->_remoteFundingEntityManager = $remoteFundingEntityManager;
    $this->_eventDispatcher = $eventDispatcher;
    $this->_authorizeRequestEventName = FundingEvents::REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REQUEST_INIT_EVENT_NAME;
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
      case SubmitFormEvent::ACTION_SHOW_FORM:
        Assert::notNull($event->getForm());
        Assert::keyExists($event->getForm()['data'], 'applicationProcessId');
        Assert::integer($event->getForm()['data']['applicationProcessId']);
        $result['jsonSchema'] = $event->getForm()['jsonSchema'];
        $result['uiSchema'] = $event->getForm()['uiSchema'];
        $result['data'] = $event->getForm()['data'];
        break;

      case SubmitFormEvent::ACTION_SHOW_VALIDATION:
        Assert::notEmpty($event->getErrors());
        $result['errors'] = $event->getErrors();
        break;

      case SubmitFormEvent::ACTION_CLOSE_FORM:
        break;

      default:
        throw new \API_Exception(sprintf('Unknown action "%s"', $event->getAction()));
    }
  }

  /**
   * @throws \API_Exception
   */
  private function createEvent(): SubmitFormEvent {
    Assert::notNull($this->remoteContactId);
    /** @var array<string, mixed>&array{id: int, funding_case_id: int} $applicationProcess */
    $applicationProcess = $this->_remoteFundingEntityManager->getById(
      'FundingApplicationProcess', $this->getApplicationProcessId(), $this->remoteContactId
    );
    /** @var array<string, mixed>&array{id: int, funding_case_type_id: int} $fundingCase */
    $fundingCase = $this->_remoteFundingEntityManager->getById(
      'FundingCase', $applicationProcess['funding_case_id'], $this->remoteContactId
    );
    $fundingCaseType = $this->_remoteFundingEntityManager->getById(
      'FundingCaseType', $fundingCase['funding_case_type_id'], $this->remoteContactId
    );

    return SubmitFormEvent::fromApiRequest($this, $this->getExtraParams() + [
      'applicationProcess' => $applicationProcess,
      'fundingCase' => $fundingCase,
      'fundingCaseType' => $fundingCaseType,
    ]);
  }

  public function getApplicationProcessId(): int {
    Assert::keyExists($this->data, 'applicationProcessId');
    Assert::integer($this->data['applicationProcessId']);

    return $this->data['applicationProcessId'];
  }

}