<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Form;

use Civi\Funding\Entity\FullApplicationProcessStatus;

interface ApplicationSubmitActionsFactoryInterface {

  public const SERVICE_TAG = 'funding.application.submit_actions_factory';

  /**
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string, array{
   *   label: string,
   *   confirm: string|null,
   *   properties: array<string, mixed>,
   * }>
   *   Map of action names to button labels, confirm messages, and properties.
   */
  public function createInitialSubmitActions(array $permissions): array;

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $statusList
   *     Status of other application processes in same funding case indexed by ID.
   * @phpstan-param array<string> $permissions
   *
   * @phpstan-return array<string, array{
   *   label: string,
   *   confirm: string|null,
   *   properties: array<string, mixed>&array{needsFormData?: bool},
   * }>
   *   Map of action names to button labels, confirm messages, and properties.
   *   needsFormData is FALSE if the action is applicable without form data.
   */
  public function createSubmitActions(
    FullApplicationProcessStatus $status,
    array $statusList,
    array $permissions
  ): array;

  /**
   * @phpstan-param array<int, \Civi\Funding\Entity\FullApplicationProcessStatus> $statusList
   *     Status of other application processes in same funding case indexed by ID.
   * @phpstan-param array<string> $permissions
   *
   * @return bool
   *   true if an action that allows to edit the application details is
   *   available.
   */
  public function isEditAllowed(FullApplicationProcessStatus $status, array $statusList, array $permissions): bool;

}
