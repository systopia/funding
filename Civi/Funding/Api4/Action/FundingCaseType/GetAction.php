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
use Civi\Funding\Util\ArrayUtil;

final class GetAction extends DAOGetAction {

  use IsFieldSelectedTrait;

  private ?FundingAttachmentManagerInterface $attachmentManager;

  public function __construct(?FundingAttachmentManagerInterface $attachmentManager = NULL) {
    parent::__construct(FundingCaseType::getEntityName(), 'get');
    $this->attachmentManager = $attachmentManager;
  }

  public function _run(Result $result): void {
    $transferContractTemplateFileIdSelected =
      ArrayUtil::removeFirstOccurrence($this->select, 'transfer_contract_template_file_id');

    $paymentInstructionTemplateFileIdSelected =
      ArrayUtil::removeFirstOccurrence($this->select, 'payment_instruction_template_file_id');

    $paybackClaimTemplateFileIdSelected =
      ArrayUtil::removeFirstOccurrence($this->select, 'payback_claim_template_file_id');

    $drawdownSubmitConfirmationTemplateFileIdSelected =
      ArrayUtil::removeFirstOccurrence($this->select, 'drawdown_submit_confirmation_template_file_id');

    parent::_run($result);

    $idSelected = $this->isFieldSelected('id');

    if ($idSelected && $transferContractTemplateFileIdSelected) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $record['transfer_contract_template_file_id'] = $this->getFileId(
          // @phpstan-ignore argument.type
          $record['id'],
          FileTypeNames::TRANSFER_CONTRACT_TEMPLATE
        );
      }
    }

    if ($idSelected && $paymentInstructionTemplateFileIdSelected) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $record['payment_instruction_template_file_id'] = $this->getFileId(
          // @phpstan-ignore argument.type
          $record['id'],
          FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE
        );
      }
    }

    if ($idSelected && $paybackClaimTemplateFileIdSelected) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $record['payback_claim_template_file_id'] = $this->getFileId(
          // @phpstan-ignore argument.type
          $record['id'],
          FileTypeNames::PAYBACK_CLAIM_TEMPLATE
        );
      }
    }

    if ($idSelected && $drawdownSubmitConfirmationTemplateFileIdSelected) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $record['drawdown_submit_confirmation_template_file_id'] = $this->getFileId(
          // @phpstan-ignore argument.type
          $record['id'],
          FileTypeNames::DRAWDOWN_SUBMIT_CONFIRMATION_TEMPLATE
        );
      }
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getFileId(int $fundingCaseId, string $fileTypeName): ?int {
    $attachment = $this->getAttachmentManager()->getLastByFileType(
      'civicrm_funding_case_type',
      $fundingCaseId,
      $fileTypeName
    );

    return $attachment?->getId();
  }

  private function getAttachmentManager(): FundingAttachmentManagerInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->attachmentManager ??= \Civi::service(FundingAttachmentManagerInterface::class);
  }

}
