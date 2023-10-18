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

namespace Civi\Funding\FundingCase;

use Civi\Api4\FundingCasePermissionsCache;
use Civi\RemoteTools\Api4\Api4Interface;

final class FundingCasePermissionsCacheManager {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-param array<string> $permissions
   *
   * @throws \CRM_Core_Exception
   */
  public function add(int $fundingCaseId, int $contactId, bool $remote, array $permissions): void {
    $this->api4->createEntity(
      FundingCasePermissionsCache::getEntityName(),
      [
        'funding_case_id' => $fundingCaseId,
        'contact_id' => $contactId,
        'is_remote' => $remote,
        'permissions' => $permissions,
      ],
      ['checkPermissions' => FALSE]
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function clearByContactId(int ...$contactIds): void {
    $this->api4->execute(FundingCasePermissionsCache::getEntityName(), 'update', [
      'values' => ['permissions' => NULL],
      'where' => [['contact_id', 'IN', $contactIds]],
      'checkPermissions' => FALSE,
    ]);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function clearByFundingCaseId(int $fundingCaseId): void {
    $this->api4->execute(FundingCasePermissionsCache::getEntityName(), 'update', [
      'values' => ['permissions' => NULL],
      'where' => [['funding_case_id', '=', $fundingCaseId]],
      'checkPermissions' => FALSE,
    ]);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function deleteByContactId(int $contactId): void {
    $this->api4->execute(FundingCasePermissionsCache::getEntityName(), 'delete', [
      'where' => [['contact_id', '=', $contactId]],
      'checkPermissions' => FALSE,
    ]);
  }

  /**
   * @phpstan-param array<string> $permissions
   *
   * @throws \CRM_Core_Exception
   */
  public function update(int $id, array $permissions): void {
    $this->api4->updateEntity(
      FundingCasePermissionsCache::getEntityName(),
      $id,
      ['permissions' => $permissions],
      ['checkPermissions' => FALSE],
    );
  }

}
