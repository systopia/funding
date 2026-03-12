<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseType\MetaData;

/**
 * Do not implement directly, extend AbstractFundingCaseTypeMetaData instead.
 */
interface FundingCaseTypeMetaDataInterface {

  public function getName(): string;

  public function getApplicationProcessAction(string $name): ?ApplicationProcessAction;

  /**
   * @return non-empty-array<string, ApplicationProcessAction>
   *   Must contain actions:
   *     - that shall be shown on the application form to applicants.
   *       (The order determines the order on the form.)
   *     - that shall be available as SearchKit action for reviewers.
   *     - that shall perform a deletion or a restore.
   */
  public function getApplicationProcessActions(): array;

  public function getApplicationProcessStatus(string $name): ?ApplicationProcessStatus;

  /**
   * @return non-empty-array<string, ApplicationProcessStatus>
   *   Mapping of name to ApplicationProcessStatus.
   */
  public function getApplicationProcessStatuses(): array;

  public function getCostItemType(string $name): ?CostItemTypeInterface;

  /**
   * @return array<string, CostItemTypeInterface>
   *   Mapping of name to CostItemTypeInterface.
   */
  public function getCostItemTypes(): array;

  public function getFundingCaseAction(string $actionName): ?FundingCaseAction;

  /**
   * @return array<string, FundingCaseAction>
   *   Actions available to applicants for the funding case. Only relevant for
   *   combined applications. Funding case types for non-combined application
   *   can return an empty array.
   */
  public function getFundingCaseActions(): array;

  public function getResourcesItemType(string $name): ?ResourcesItemTypeInterface;

  /**
   * @return array<string, ResourcesItemTypeInterface>
   *   Mapping of name to ResourcesItemTypeInterface.
   */
  public function getResourcesItemTypes(): array;

  /**
   * @return bool
   *   TRUE if it is allowed to accept a clearing calculative without checking
   *   each clearing item individually. The amount admitted of items will be set
   *   to the amount cleared, if not set.
   */
  public function isGeneralClearingAdmitAllowed(): bool;

  /**
   * @return bool
   *   TRUE if the final drawdown created on finish clearing (if amount paid out
   *   and amount admitted aren't equal) shall be accepted by default. FALSE if
   *   the final drawdown shall remain in status "new".
   */
  public function isFinalDrawdownAcceptedByDefault(): bool;

}
