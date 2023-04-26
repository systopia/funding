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

namespace Civi\Funding\PayoutProcess\Handler;

use Civi\Funding\DocumentRender\DocumentRendererInterface;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\FileTypeIds;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\PayoutProcess\Command\PaymentInstructionRenderCommand;
use Civi\Funding\PayoutProcess\Command\PaymentInstructionRenderResult;

final class PaymentInstructionRenderHandler implements PaymentInstructionRenderHandlerInterface {

  private FundingAttachmentManagerInterface $attachmentManager;

  private DocumentRendererInterface $documentRenderer;

  public function __construct(
    FundingAttachmentManagerInterface $attachmentManager,
    DocumentRendererInterface $documentRenderer
  ) {
    $this->attachmentManager = $attachmentManager;
    $this->documentRenderer = $documentRenderer;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(PaymentInstructionRenderCommand $command): PaymentInstructionRenderResult {
    return new PaymentInstructionRenderResult(
      $this->documentRenderer->render(
        $this->getTemplateFile($command->getFundingCaseType()),
        'FundingPaymentInstruction',
        $command->getDrawdown()->getId(),
        [
          'drawdown' => $command->getDrawdown(),
          'bankAccount' => $command->getBankAccount(),
          'payoutProcess' => $command->getPayoutProcess(),
          'fundingCase' => $command->getFundingCase(),
          'fundingCaseType' => $command->getFundingCaseType(),
          'fundingProgram' => $command->getFundingProgram(),
        ],
      ), $this->documentRenderer->getMimeType(),
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getTemplateFile(FundingCaseTypeEntity $fundingCaseType): string {
    $attachment = $this->attachmentManager->getLastByFileType(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      FileTypeIds::PAYMENT_INSTRUCTION_TEMPLATE,
    );

    if (NULL === $attachment) {
      throw new \RuntimeException(sprintf(
        'No payment instruction template for funding case type "%s" found.',
        $fundingCaseType->getName()
      ));
    }

    return $attachment->getPath();
  }

}
