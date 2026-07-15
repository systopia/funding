<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingApplicationProcess\GetAllowedActionsInitialByFundingCaseAction;
use Civi\Funding\Form\Application\ApplicationSubmitActionsFactoryInterface;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;

/**
 * Get allowed initial application process actions for given funding case IDs.
 */
final class GetAllowedActionsInitialByFundingCaseActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingApplicationProcess';

  public function __construct(
    private readonly ApplicationSubmitActionsFactoryInterface $submitActionsFactory,
    private readonly FundingCaseManager $fundingCaseManager,
  ) {}

  /**
   * @return array<int, array<string, array{label: string, confirm: string|null}>>
   *   Map of action names to button labels and confirm messages indexed by
   *   funding case ID.
   */
  public function getAllowedActionsInitialByFundingCase(GetAllowedActionsInitialByFundingCaseAction $action): array {
    $actions = [];
    foreach ($action->getFundingCaseIds() as $id) {
      $actions[$id] = $this->getAllowedInitialActionsByFundingCaseId($id);
    }

    return $actions;
  }

  /**
   * @phpstan-return array<string, array{label: string, confirm: string|null}>
   *    Map of action names to button labels and confirm messages.
   */
  private function getAllowedInitialActionsByFundingCaseId(int $fundingCaseId): array {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($fundingCaseId);
    if (NULL === $fundingCaseBundle) {
      return [];
    }

    $actions = $this->submitActionsFactory->getInitialSubmitActions(
      $fundingCaseBundle->getFundingCase()->getPermissions(),
      $fundingCaseBundle->getFundingCaseType(),
      $fundingCaseBundle->getFundingCase()
    );

    $result = [];
    foreach ($actions as $action) {
      $result[$action->getName()] = [
        'label' => $action->getLabel(),
        'confirm' => $action->getConfirmMessage(),
      ];
    }

    return $result;
  }

}
