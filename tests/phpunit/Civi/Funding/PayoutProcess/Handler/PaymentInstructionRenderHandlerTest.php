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
use Civi\Funding\EntityFactory\AttachmentFactory;
use Civi\Funding\EntityFactory\DrawdownFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\EntityFactory\PayoutProcessFactory;
use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\PayoutProcess\BankAccount;
use Civi\Funding\PayoutProcess\Command\PaymentInstructionRenderCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Handler\PaymentInstructionRenderHandler
 * @covers \Civi\Funding\PayoutProcess\Command\PaymentInstructionRenderCommand
 */
final class PaymentInstructionRenderHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingAttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  /**
   * @var \Civi\Funding\DocumentRender\DocumentRendererInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $documentRendererMock;

  private PaymentInstructionRenderHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->documentRendererMock = $this->createMock(DocumentRendererInterface::class);
    $this->handler = new PaymentInstructionRenderHandler(
      $this->attachmentManagerMock,
      $this->documentRendererMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $paymentInstructionAttachment = AttachmentFactory::create([
      'entity_table' => 'civicrm_funding_case_type',
      'entity_id' => FundingCaseTypeFactory::DEFAULT_ID,
    ]);
    $this->attachmentManagerMock->method('getLastByFileType')
      ->with(
        'civicrm_funding_case_type',
        FundingCaseTypeFactory::DEFAULT_ID,
        FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE
      )->willReturn($paymentInstructionAttachment);

    $this->documentRendererMock->expects(static::once())->method('render')
      ->with(
        $paymentInstructionAttachment->getPath(),
        'FundingPaymentInstruction',
        DrawdownFactory::DEFAULT_ID,
        [
          'drawdown' => $command->getDrawdown(),
          'bankAccount' => $command->getBankAccount(),
          'payoutProcess' => $command->getPayoutProcess(),
          'fundingCase' => $command->getFundingCase(),
          'fundingCaseType' => $command->getFundingCaseType(),
          'fundingProgram' => $command->getFundingProgram(),
        ],
      );
    $this->handler->handle($command);
  }

  public function testHandleNoTemplate(): void {
    $command = $this->createCommand();
    $this->attachmentManagerMock->method('getLastByFileType')
      ->with(
        'civicrm_funding_case_type',
        FundingCaseTypeFactory::DEFAULT_ID,
        FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE
      )->willReturn(NULL);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf(
      'No payment instruction template for funding case type "%s" found.',
      FundingCaseTypeFactory::DEFAULT_NAME,
    ));
    $this->handler->handle($command);
  }

  private function createCommand(): PaymentInstructionRenderCommand {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $payoutProcess = PayoutProcessFactory::create();
    $bankAccount = new BankAccount('BIC', 'reference');
    $drawdown = DrawdownFactory::create();

    return new PaymentInstructionRenderCommand(
      $drawdown,
      $bankAccount,
      $payoutProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram,
    );
  }

}
