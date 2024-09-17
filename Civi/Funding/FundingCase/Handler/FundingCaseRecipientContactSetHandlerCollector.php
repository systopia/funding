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

use Civi\Funding\FundingCase\Command\FundingCaseRecipientContactSetCommand;
use Psr\Container\ContainerInterface;

final class FundingCaseRecipientContactSetHandlerCollector implements FundingCaseRecipientContactSetHandlerInterface {

  private ContainerInterface $container;

  /**
   * @param \Psr\Container\ContainerInterface $container
   *   Handlers with funding case type name as ID.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  public function handle(FundingCaseRecipientContactSetCommand $command): void {
    /** @var \Civi\Funding\FundingCase\Handler\FundingCaseRecipientContactSetHandlerInterface $handler */
    $handler = $this->container->get($command->getFundingCaseType()->getName());
    $handler->handle($command);
  }

}
