<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingCase;

use Civi\Api4\FundingCase;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\FundingCase\Command\TransferContractRecreateCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

/**
 * @method $this setId(int $id)
 */
class RecreateTransferContractAction extends AbstractAction {

  /**
   * @var int
   * @reuired
   */
  protected ?int $id = NULL;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private TransferContractRecreateHandlerInterface $transferContractRecreateHandler;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    TransferContractRecreateHandlerInterface $transferContractRecreateHandler
  ) {
    parent::__construct(FundingCase::_getEntityName(), 'recreateTransferContract');
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
    $this->transferContractRecreateHandler = $transferContractRecreateHandler;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result): void {
    Assert::notNull($this->id);
    $fundingCase = $this->fundingCaseManager->get($this->id);
    Assert::notNull($fundingCase, E::ts('Funding case with ID "%1" not found', [1 => $this->id]));
    $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);
    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram, sprintf(
      'No permission to access funding program with ID "%d"',
      $fundingCase->getFundingProgramId()
    ));

    $this->transferContractRecreateHandler->handle(new TransferContractRecreateCommand(
      $fundingCase,
      $fundingCaseType,
      $fundingProgram,
    ));

    $result->exchangeArray($fundingCase->toArray());
  }

}
