<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\FundingCaseType;

use Civi\Api4\FundingCaseType;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\IsFieldSelectedTrait;
use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;

final class GetAction extends DAOGetAction {

  use IsFieldSelectedTrait;

  private FundingAttachmentManagerInterface $attachmentManager;

  public function __construct(FundingAttachmentManagerInterface $attachmentManager) {
    parent::__construct(FundingCaseType::getEntityName(), 'get');
    $this->attachmentManager = $attachmentManager;
  }

  public function _run(Result $result): void {
    $transferContractTemplateFileIdIndex = array_search('transfer_contract_template_file_id', $this->select, TRUE);
    if ($transferContractTemplateFileIdIndex !== FALSE) {
      unset($this->select[$transferContractTemplateFileIdIndex]);
    }

    $paymentInstructionTemplateFileIdIndex = array_search('payment_instruction_template_file_id', $this->select, TRUE);
    if ($paymentInstructionTemplateFileIdIndex !== FALSE) {
      unset($this->select[$paymentInstructionTemplateFileIdIndex]);
    }

    $paybackClaimTemplateFileIdIndex = array_search('payback_claim_template_file_id', $this->select, TRUE);
    if ($paybackClaimTemplateFileIdIndex !== FALSE) {
      unset($this->select[$paybackClaimTemplateFileIdIndex]);
    }

    parent::_run($result);

    $idSelected = $this->isFieldSelected('id');

    if ($idSelected && $transferContractTemplateFileIdIndex !== FALSE) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $record['transfer_contract_template_file_id'] = $this->getFileId(
        // @phpstan-ignore-next-line
          $record['id'],
          FileTypeNames::TRANSFER_CONTRACT_TEMPLATE
        );
      }
    }

    if ($idSelected && $paymentInstructionTemplateFileIdIndex !== FALSE) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $record['payment_instruction_template_file_id'] = $this->getFileId(
        // @phpstan-ignore-next-line
          $record['id'],
          FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE
        );
      }
    }

    if ($idSelected && $paybackClaimTemplateFileIdIndex !== FALSE) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $record['payback_claim_template_file_id'] = $this->getFileId(
          // @phpstan-ignore-next-line
          $record['id'],
          FileTypeNames::PAYBACK_CLAIM_TEMPLATE
        );
      }
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getFileId(int $fundingCaseId, string $fileTypeName): ?int {
    $attachment = $this->attachmentManager->getLastByFileType(
      'civicrm_funding_case_type',
      $fundingCaseId,
      $fileTypeName
    );

    return NULL === $attachment ? NULL : $attachment->getId();
  }

}
