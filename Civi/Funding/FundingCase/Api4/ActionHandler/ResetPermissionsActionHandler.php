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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Funding\Api4\Action\FundingCase\ResetPermissionsAction;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\FundingCasePermissionsInitializer;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class ResetPermissionsActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingCase';

  private Api4Interface $api4;

  private FundingCaseManager $fundingCaseManager;

  private FundingCasePermissionsInitializer $permissionsInitializer;

  public function __construct(
    Api4Interface $api4,
    FundingCaseManager $fundingCaseManager,
    FundingCasePermissionsInitializer $permissionsInitializer
  ) {
    $this->api4 = $api4;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->permissionsInitializer = $permissionsInitializer;
  }

  /**
   * Reset permissions to the initial funding case permissions configured in
   * the funding program.
   *
   * @throws \CRM_Core_Exception
   *
   * @phpstan-return array{}
   */
  public function resetPermissions(ResetPermissionsAction $action): array {
    $fundingCase = $this->fundingCaseManager->get($action->getId());
    Assert::notNull($fundingCase, E::ts('Funding case with ID "%1" not found', [1 => $action->getId()]));

    $this->api4->execute(FundingCaseContactRelation::getEntityName(), 'delete', [
      'where' => [['funding_case_id', '=', $fundingCase->getId()]],
    ]);
    $this->permissionsInitializer->initializePermissions($fundingCase);

    return [];
  }

}
