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

namespace Civi\Funding\Api4\Action\Remote\FundingCase;

use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Event\Remote\FundingCase\SubmitNewApplicationFormEvent;
use Civi\Funding\Exception\FundingException;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingCaseTypeProgramRelationChecker;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Webmozart\Assert\Assert;

/**
 * @method $this setData(array $data)
 */
class SubmitNewApplicationFormAction extends AbstractNewApplicationFormAction {

  /**
   * @var array
   * @phpstan-var array<string, mixed>
   * @required
   */
  protected ?array $data = NULL;

  public function __construct(
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    CiviEventDispatcherInterface $eventDispatcher,
    FundingCaseTypeProgramRelationChecker $relationChecker
  ) {
    parent::__construct(
      'submitNewApplicationForm',
      $fundingCaseTypeManager,
      $fundingProgramManager,
      $eventDispatcher,
      $relationChecker,
    );
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $this->assertFundingCaseTypeAndProgramRelated($this->getFundingCaseTypeId(), $this->getFundingProgramId());
    $event = $this->createEvent();
    $this->dispatchEvent($event);

    $result->debug['event'] = $event->getDebugOutput();

    if (NULL === $event->getAction()) {
      throw new FundingException('Form not handled');
    }

    $result->rowCount = 1;
    $result->exchangeArray(['action' => $event->getAction()]);
    if (NULL !== $event->getMessage()) {
      $result['message'] = $event->getMessage();
    }

    switch ($event->getAction()) {
      case SubmitNewApplicationFormEvent::ACTION_SHOW_FORM:
        Assert::notNull($event->getForm());
        if (isset($event->getForm()->getData()['applicationProcessId'])) {
          // Application is persisted
          Assert::integer($event->getForm()->getData()['applicationProcessId']);
        }
        else {
          Assert::keyExists($event->getForm()->getData(), 'fundingCaseTypeId');
          Assert::integer($event->getForm()->getData()['fundingCaseTypeId']);
          Assert::keyExists($event->getForm()->getData(), 'fundingProgramId');
          Assert::integer($event->getForm()->getData()['fundingProgramId']);
        }
        $result['jsonSchema'] = $event->getForm()->getJsonSchema();
        $result['uiSchema'] = $event->getForm()->getUiSchema();
        $result['data'] = $event->getForm()->getData();
        break;

      case SubmitNewApplicationFormEvent::ACTION_SHOW_VALIDATION:
        Assert::notEmpty($event->getErrors());
        $result['errors'] = $event->getErrors();
        break;

      case SubmitNewApplicationFormEvent::ACTION_CLOSE_FORM:
        break;

      default:
        throw new FundingException(sprintf('Unknown action "%s"', $event->getAction()));
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createEvent(): SubmitNewApplicationFormEvent {
    return SubmitNewApplicationFormEvent::fromApiRequest(
      $this,
      $this->createEventParams($this->getFundingCaseTypeId(), $this->getFundingProgramId()),
    );
  }

  public function getFundingProgramId(): int {
    Assert::notNull($this->data);
    Assert::keyExists($this->data, 'fundingProgramId');
    Assert::integer($this->data['fundingProgramId']);

    return $this->data['fundingProgramId'];
  }

  public function getFundingCaseTypeId(): int {
    Assert::notNull($this->data);
    Assert::keyExists($this->data, 'fundingCaseTypeId');
    Assert::integer($this->data['fundingCaseTypeId']);

    return $this->data['fundingCaseTypeId'];
  }

}
