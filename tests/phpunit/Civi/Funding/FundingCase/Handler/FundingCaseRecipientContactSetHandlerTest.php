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

namespace Civi\Funding\FundingCase\Handler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseRecipientContactSetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Recipients\PossibleRecipientsForChangeLoaderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Handler\FundingCaseRecipientContactSetHandler
 * @covers \Civi\Funding\FundingCase\Command\FundingCaseRecipientContactSetCommand
 */
final class FundingCaseRecipientContactSetHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $actionsDeterminerMock;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  private FundingCaseRecipientContactSetHandler $handler;

  /**
   * @var \Civi\Funding\FundingCase\Recipients\PossibleRecipientsForChangeLoaderInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $possibleRecipientsLoaderMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(FundingCaseActionsDeterminerInterface::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->possibleRecipientsLoaderMock = $this->createMock(PossibleRecipientsForChangeLoaderInterface::class);
    $this->handler = new FundingCaseRecipientContactSetHandler(
      $this->actionsDeterminerMock,
      $this->fundingCaseManagerMock,
      $this->possibleRecipientsLoaderMock
    );
  }

  public function testHandle(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'set-recipient-contact',
        $command->getFundingCase()->getStatus(),
        $command->getApplicationProcessStatusList(),
        $command->getFundingCase()->getPermissions()
      )
      ->willReturn(TRUE);

    $this->possibleRecipientsLoaderMock->method('getPossibleRecipients')
      ->with($command->getFundingCase(), $command->getFundingCaseType(), $command->getFundingProgram())
      ->willReturn([1234 => 'New Recipient']);

    $this->fundingCaseManagerMock->expects(static::once())->method('update')
      ->with($command->getFundingCase());

    $this->handler->handle($command);
    static::assertSame(1234, $command->getFundingCase()->getRecipientContactId());
  }

  public function testHandleNotInPossibleContacts(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'set-recipient-contact',
        $command->getFundingCase()->getStatus(),
        $command->getApplicationProcessStatusList(),
        $command->getFundingCase()->getPermissions()
      )
      ->willReturn(TRUE);

    $this->possibleRecipientsLoaderMock->method('getPossibleRecipients')
      ->with($command->getFundingCase(), $command->getFundingCaseType(), $command->getFundingProgram())
      ->willReturn([$command->getRecipientContactId() + 1 => 'Some Recipient']);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid recipient contact ID');
    $this->handler->handle($command);
  }

  public function testHandleUnauthorized(): void {
    $command = $this->createCommand();
    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with(
        'set-recipient-contact',
        $command->getFundingCase()->getStatus(),
        $command->getApplicationProcessStatusList(),
        $command->getFundingCase()->getPermissions()
      )
      ->willReturn(FALSE);

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Changing the recipient contact of this funding case is not allowed.');
    $this->handler->handle($command);
  }

  private function createCommand(): FundingCaseRecipientContactSetCommand {
    $fundingCase = FundingCaseFactory::createFundingCase(['creation_contact_id' => 1, 'recipient_contact_id' => 2]);
    $recipientContactId = 1234;
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingProgram = FundingProgramFactory::createFundingProgram();

    return new FundingCaseRecipientContactSetCommand(
      $fundingCase,
      $recipientContactId,
      [22 => new FullApplicationProcessStatus('eligible', TRUE, TRUE)],
      $fundingCaseType,
      $fundingProgram
    );
  }

}
