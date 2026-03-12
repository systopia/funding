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

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingCase\GetPossibleRecipientsAction;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Recipients\PossibleRecipientsForChangeLoaderInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class GetPossibleRecipientsActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingCase';

  private FundingCaseManager $fundingCaseManager;

  private PossibleRecipientsForChangeLoaderInterface $possibleRecipientsLoader;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    PossibleRecipientsForChangeLoaderInterface $possibleRecipientsLoader
  ) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->possibleRecipientsLoader = $possibleRecipientsLoader;
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * @phpstan-return list<array{id: int, name: string}>
   */
  public function getPossibleRecipients(GetPossibleRecipientsAction $action): array {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($action->getId());
    Assert::notNull($fundingCaseBundle, E::ts('Funding case with ID "%1" not found', [1 => $action->getId()]));

    $possibleRecipients = $this->possibleRecipientsLoader->getPossibleRecipients($fundingCaseBundle);
    $result = [];
    foreach ($possibleRecipients as $id => $name) {
      $result[] = [
        'id' => $id,
        'name' => $name,
      ];
    }

    return $result;
  }

}
