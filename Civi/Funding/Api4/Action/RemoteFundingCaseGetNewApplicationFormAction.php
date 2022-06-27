<?php
declare(strict_types = 1);

namespace Civi\Funding\Api4\Action;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\Traits\RemoteFundingActionContactIdRequiredTrait;
use Civi\Funding\Event\FundingEvents;
use Civi\Funding\Event\RemoteFundingCaseGetNewApplicationFormEvent;
use Civi\Funding\Remote\RemoteFundingEntityManager;
use Civi\Funding\Remote\RemoteFundingEntityManagerInterface;
use Webmozart\Assert\Assert;

/**
 * @method void setFundingProgramId(int $fundingProgramId)
 * @method void setFundingCaseTypeId(int $fundingCaseTypeId)
 */
final class RemoteFundingCaseGetNewApplicationFormAction extends AbstractRemoteFundingAction {

  use RemoteFundingActionContactIdRequiredTrait;

  /**
   * @var int
   * @required
   */
  protected int $fundingProgramId;

  /**
   * @var int
   * @required
   */
  protected int $fundingCaseTypeId;

  private RemoteFundingEntityManagerInterface $_remoteFundingEntityManager;

  public function __construct(
    RemoteFundingEntityManagerInterface $remoteFundingEntityManager = NULL,
    CiviEventDispatcher $eventDispatcher = NULL
  ) {
    parent::__construct('RemoteFundingCase', 'getNewApplicationForm');
    $this->_remoteFundingEntityManager = $remoteFundingEntityManager ?? RemoteFundingEntityManager::getInstance();
    $this->_eventDispatcher = $eventDispatcher ?? \Civi::dispatcher();
    $this->_authorizeRequestEventName = FundingEvents::REMOTE_REQUEST_AUTHORIZE_EVENT_NAME;
    $this->_initRequestEventName = FundingEvents::REMOTE_REQUEST_INIT_EVENT_NAME;
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
    if ([] === $event->getJsonSchema() || [] === $event->getUiSchema()) {
      throw new \API_Exception('Invalid fundingProgramId or fundingCaseTypeId', 'invalid_arguments');
    }

    Assert::keyExists($event->getData(), 'fundingCaseTypeId');
    Assert::same($event->getData()['fundingCaseTypeId'], $this->fundingCaseTypeId);
    Assert::keyExists($event->getData(), 'fundingProgramId');
    Assert::same($event->getData()['fundingProgramId'], $this->fundingProgramId);

    $result->rowCount = 1;
    $result->exchangeArray([
      'jsonSchema' => $event->getJsonSchema(),
      'uiSchema' => $event->getUiSchema(),
      'data' => $event->getData(),
    ]);
  }

  /**
   * @throws \API_Exception
   */
  private function createEvent(): RemoteFundingCaseGetNewApplicationFormEvent {
    Assert::notNull($this->remoteContactId);
    $fundingCaseType = $this->_remoteFundingEntityManager
      ->getById('FundingCaseType', $this->fundingCaseTypeId, $this->remoteContactId);
    $fundingProgram = $this->_remoteFundingEntityManager
      ->getById('FundingProgram', $this->fundingProgramId, $this->remoteContactId);

    return RemoteFundingCaseGetNewApplicationFormEvent::fromApiRequest($this, $this->getExtraParams() + [
      'fundingCaseType' => $fundingCaseType,
      'fundingProgram' => $fundingProgram,
    ]);
  }

}
