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

namespace Civi\Funding\FundingCase\Handler\Decorator;

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Event\FundingCase\FundingCaseAmountApprovedUpdatedEvent;
use Civi\Funding\FundingCase\Command\FundingCaseUpdateAmountApprovedCommand;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;

/**
 * @codeCoverageIgnore
 */
final class FundingCaseUpdateAmountApprovedEventDecorator implements FundingCaseUpdateAmountApprovedHandlerInterface {

  private FundingCaseUpdateAmountApprovedHandlerInterface $handler;

  private CiviEventDispatcherInterface $eventDispatcher;

  public function __construct(
    FundingCaseUpdateAmountApprovedHandlerInterface $handler,
    CiviEventDispatcherInterface $eventDispatcher
  ) {
    $this->handler = $handler;
    $this->eventDispatcher = $eventDispatcher;
  }

  public function handle(FundingCaseUpdateAmountApprovedCommand $command): void {
    $amountBefore = $command->getFundingCase()->getAmountApproved();
    $this->handler->handle($command);

    if ($command->getFundingCase()->getAmountApproved() !== $amountBefore) {
      $event = new FundingCaseAmountApprovedUpdatedEvent(
        $command->getFundingCase(), $command->getFundingCaseType(), $command->getFundingProgram()
      );

      $this->eventDispatcher->dispatch(FundingCaseAmountApprovedUpdatedEvent::class, $event);
    }
  }

}
