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

namespace Civi\Funding\TransferContract\Handler;

use Civi\Funding\DocumentRender\DocumentRendererInterface;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\AttachmentFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\FileTypeIds;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\TransferContract\Command\TransferContractRenderCommand;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\TransferContract\Handler\TransferContractRenderHandler
 * @covers \Civi\Funding\TransferContract\Command\TransferContractRenderCommand
 */
final class TransferContractRenderHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingAttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  /**
   * @var \Civi\Funding\DocumentRender\DocumentRendererInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $documentRendererMock;

  /**
   * @var \Civi\Funding\TransferContract\Handler\TransferContractRenderHandler
   */
  private TransferContractRenderHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->documentRendererMock = $this->createMock(DocumentRendererInterface::class);
    $this->handler = new TransferContractRenderHandler(
      $this->attachmentManagerMock,
      $this->documentRendererMock,
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $transferContractAttachment = AttachmentFactory::create([
      'entity_table' => 'civicrm_funding_case_type',
      'entity_id' => FundingCaseTypeFactory::DEFAULT_ID,
    ]);
    $this->attachmentManagerMock->method('getLastByFileType')
      ->with('civicrm_funding_case_type', FundingCaseTypeFactory::DEFAULT_ID, FileTypeIds::TRANSFER_CONTRACT_TEMPLATE)
      ->willReturn($transferContractAttachment);

    $this->documentRendererMock->expects(static::once())->method('render')
      ->with(
        $transferContractAttachment->getPath(),
        'TransferContract',
        FundingCaseFactory::DEFAULT_ID,
        [
          'fundingCase' => $command->getFundingCase(),
          'eligibleApplicationProcesses' => $command->getEligibleApplicationProcesses(),
          'fundingCaseType' => $command->getFundingCaseType(),
          'fundingProgram' => $command->getFundingProgram(),
        ],
      );
    $this->handler->handle($command);
  }

  public function testHandleNoTemplate(): void {
    $command = $this->createCommand();
    $this->attachmentManagerMock->method('getLastByFileType')
      ->with('civicrm_funding_case_type', FundingCaseTypeFactory::DEFAULT_ID, FileTypeIds::TRANSFER_CONTRACT_TEMPLATE)
      ->willReturn(NULL);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage(sprintf(
      'No transfer contract template for funding case type "%s" found.',
      FundingCaseTypeFactory::DEFAULT_NAME,
    ));
    $this->handler->handle($command);
  }

  private function createCommand(): TransferContractRenderCommand {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'amount_requested' => 88.99,
      'is_eligible' => TRUE,
    ]);
    return new TransferContractRenderCommand(
      [$applicationProcessBundle->getApplicationProcess()],
      $applicationProcessBundle->getFundingCase(),
      $applicationProcessBundle->getFundingCaseType(),
      $applicationProcessBundle->getFundingProgram(),
    );
  }

}
