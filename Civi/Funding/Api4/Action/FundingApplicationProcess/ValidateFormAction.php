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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\FundingActionContactIdSessionTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Webmozart\Assert\Assert;

/**
 * @method $this setData(array $data)
 * @method $this setId(int $id)
 */
final class ValidateFormAction extends AbstractAction {

  use FundingActionContactIdSessionTrait;

  /**
   * @var array
   * @phpstan-var array<string, mixed>
   * @required
   */
  protected ?array $data = NULL;

  /**
   * @var int
   * @required
   */
  protected ?int $id = NULL;

  protected ApplicationProcessManager $_applicationProcessManager;

  protected FundingProgramManager $_fundingProgramManager;

  protected FundingCaseManager $_fundingCaseManager;

  protected FundingCaseTypeManager $_fundingCaseTypeManager;

  protected ApplicationFormValidateHandlerInterface $_validateFormHandler;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    FundingProgramManager $fundingProgramManager,
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    ApplicationFormValidateHandlerInterface $validateFormHandler
  ) {
    parent::__construct(FundingApplicationProcess::_getEntityName(), 'validateForm');
    $this->_applicationProcessManager = $applicationProcessManager;
    $this->_fundingProgramManager = $fundingProgramManager;
    $this->_fundingCaseManager = $fundingCaseManager;
    $this->_fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->_validateFormHandler = $validateFormHandler;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $command = $this->createCommand();
    $commandResult = $this->_validateFormHandler->handle($command);

    $result['valid'] = $commandResult->isValid();
    $result['data'] = $commandResult->getData();
    $result['errors'] = [] === $commandResult->getErrors()
      ? new \stdClass() : $commandResult->getErrors();
  }

  /**
   * @throws \API_Exception
   */
  protected function createCommand(): ApplicationFormValidateCommand {
    Assert::notNull($this->id);
    Assert::notNull($this->data);
    $applicationProcess = $this->_applicationProcessManager->get($this->id);
    Assert::notNull($applicationProcess);
    $fundingCase = $this->_fundingCaseManager->get($applicationProcess->getFundingCaseId());
    Assert::notNull($fundingCase);
    $fundingCaseType = $this->_fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);
    $fundingProgram = $this->_fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram);

    return new ApplicationFormValidateCommand(
      $applicationProcess, $fundingCase, $fundingCaseType, $fundingProgram, $this->data
    );
  }

}
