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
use Civi\Funding\PayoutProcess\Command\DrawdownDocumentRenderCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\PayoutProcess\Handler\DrawdownDocumentRenderHandler
 * @covers \Civi\Funding\PayoutProcess\Command\DrawdownDocumentRenderCommand
 */
final class DrawdownDocumentRenderHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingAttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  /**
   * @var \Civi\Funding\DocumentRender\DocumentRendererInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $documentRendererMock;

  private DrawdownDocumentRenderHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->documentRendererMock = $this->createMock(DocumentRendererInterface::class);
    $this->handler = new DrawdownDocumentRenderHandler(
      $this->attachmentManagerMock,
      $this->documentRendererMock,
    );
  }

  public function testHandlePaybackClaim(): void {
    $command = $this->createCommand(-1.23);
    $paymentInstructionAttachment = AttachmentFactory::create([
      'entity_table' => 'civicrm_funding_case_type',
      'entity_id' => FundingCaseTypeFactory::DEFAULT_ID,
    ]);
    $this->attachmentManagerMock->method('getLastByFileType')
      ->with(
        'civicrm_funding_case_type',
        FundingCaseTypeFactory::DEFAULT_ID,
        FileTypeNames::PAYBACK_CLAIM_TEMPLATE
      )->willReturn($paymentInstructionAttachment);

    $this->documentRendererMock->expects(static::once())->method('render')
      ->with(
        $paymentInstructionAttachment->getPath(),
        'FundingPaybackClaim',
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

  public function testHandleNoPaybackClaimTemplate(): void {
    $command = $this->createCommand(-1.23);
    $this->attachmentManagerMock->method('getLastByFileType')
      ->with(
        'civicrm_funding_case_type',
        FundingCaseTypeFactory::DEFAULT_ID,
        FileTypeNames::PAYBACK_CLAIM_TEMPLATE
      )->willReturn(NULL);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf(
      'No payback claim template for funding case type "%s" found.',
      FundingCaseTypeFactory::DEFAULT_NAME,
    ));
    $this->handler->handle($command);
  }

  public function testHandlePaymentInstruction(): void {
    $command = $this->createCommand(1.23);
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

  public function testHandleNoPaymentInstructionTemplate(): void {
    $command = $this->createCommand(1.23);
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

  private function createCommand(float $drawdownAmount): DrawdownDocumentRenderCommand {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();
    $payoutProcess = PayoutProcessFactory::create();
    $bankAccount = new BankAccount('BIC', 'reference');
    $drawdown = DrawdownFactory::create(['amount' => $drawdownAmount]);

    return new DrawdownDocumentRenderCommand(
      $drawdown,
      $bankAccount,
      $payoutProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram,
    );
  }

}
