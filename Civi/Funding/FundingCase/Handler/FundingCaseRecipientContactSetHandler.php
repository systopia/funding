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
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActionsDeterminerInterface;
use Civi\Funding\FundingCase\Command\FundingCaseRecipientContactSetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class FundingCaseRecipientContactSetHandler implements FundingCaseRecipientContactSetHandlerInterface {

  private FundingCaseActionsDeterminerInterface $actionsDeterminer;

  private FundingCaseManager $fundingCaseManager;

  private PossibleRecipientsLoaderInterface $possibleRecipientsLoader;

  public function __construct(
    FundingCaseActionsDeterminerInterface $actionsDeterminer,
    FundingCaseManager $fundingCaseManager,
    PossibleRecipientsLoaderInterface $possibleRecipientsLoader
  ) {
    $this->actionsDeterminer = $actionsDeterminer;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function handle(FundingCaseRecipientContactSetCommand $command): void {
    $fundingCase = $command->getFundingCase();
    $this->assertAuthorized($command);

    $possibleRecipients = $this->possibleRecipientsLoader->getPossibleRecipients(
      $fundingCase->getCreationContactId(),
      $command->getFundingProgram()
    );
    Assert::keyExists($possibleRecipients, $command->getRecipientContactId(), 'Invalid recipient contact ID');

    $fundingCase->setRecipientContactId($command->getRecipientContactId());
    $this->fundingCaseManager->update($fundingCase);
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function assertAuthorized(FundingCaseRecipientContactSetCommand $command): void {
    if (!$this->actionsDeterminer->isActionAllowed(
      FundingCaseActions::SET_RECIPIENT_CONTACT,
      $command->getFundingCase()->getStatus(),
      $command->getApplicationProcessStatusList(),
      $command->getFundingCase()->getPermissions(),
    )) {
      throw new UnauthorizedException(E::ts('Changing the recipient contact of this funding case is not allowed.'));
    }
  }

}
