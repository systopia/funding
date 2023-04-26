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

namespace Civi\Funding\TransferContract;

use Civi\Funding\ApplicationProcess\EligibleApplicationProcessesLoader;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\FileTypeIds;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\TransferContract\Command\TransferContractRenderCommand;
use Civi\Funding\TransferContract\Handler\TransferContractRenderHandlerInterface;

class TransferContractCreator {

  private EligibleApplicationProcessesLoader $applicationProcessesLoader;

  private FundingAttachmentManagerInterface $attachmentManager;

  private TransferContractRenderHandlerInterface $transferContractRenderHandler;

  public function __construct(
    EligibleApplicationProcessesLoader $applicationProcessesLoader,
    FundingAttachmentManagerInterface $attachmentManager,
    TransferContractRenderHandlerInterface $transferContractRenderHandler
  ) {
    $this->applicationProcessesLoader = $applicationProcessesLoader;
    $this->attachmentManager = $attachmentManager;
    $this->transferContractRenderHandler = $transferContractRenderHandler;
  }

  /**
   * @throws \Civi\Funding\Exception\FundingException
   * @throws \CRM_Core_Exception
   */
  public function createTransferContract(
    FundingCaseEntity $fundingCase,
    FundingCaseTypeEntity $fundingCaseType,
    FundingProgramEntity $fundingProgram
  ): void {
    $createTransferContractCommand = new TransferContractRenderCommand(
      $this->applicationProcessesLoader->getEligibleProcessesForContract($fundingCase),
      $fundingCase,
      $fundingCaseType,
      $fundingProgram,
    );
    $result = $this->transferContractRenderHandler->handle($createTransferContractCommand);

    // There might be only one transfer contract for a funding case.
    $this->attachmentManager->attachFileUniqueByFileType(
      'civicrm_funding_case',
      $fundingCase->getId(),
      FileTypeIds::TRANSFER_CONTRACT,
      $result->getFilename(),
      $result->getMimeType(),
      [
        'name' => sprintf(
          'transfer-contract.%d.%s',
          $fundingCase->getId(),
          pathinfo($result->getFilename(), PATHINFO_EXTENSION)
        ),
      ],
    );
  }

}
