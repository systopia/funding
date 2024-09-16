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

namespace Civi\Funding;

use Civi\Api4\FundingCasePermissionsCache;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandler;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

abstract class AbstractFundingHeadlessTestCase extends TestCase implements HeadlessInterface, TransactionalInterface {

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    // A class has to be registered before being used in any test, otherwise
    // ClockMock has no effect. Thus, we do it here when necessary.
    ClockMock::register(FundingCaseManager::class);
    ClockMock::register(ApplicationProcessManager::class);
    ClockMock::register(ApplicationSnapshotCreateHandler::class);
    ClockMock::register(ClearingProcessManager::class);
  }

  public static function tearDownAfterClass(): void {
    parent::tearDownAfterClass();
    ClockMock::withClockMock(FALSE);
  }

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      // Required for managed entities to be available
      ->install('activity-entity')
      ->install('external-file')
      ->install('org.civicrm.search_kit')
      ->install('de.systopia.xcm')
      ->install('de.systopia.identitytracker')
      ->install('de.systopia.remotetools')
      ->install('de.systopia.civioffice')
      ->install('org.project60.banking')
      ->installMe(__DIR__)
      ->apply();
  }

  protected function setUp(): void {
    parent::setUp();
    // @phpstan-ignore-next-line
    \CRM_Core_Config::singleton()->userFrameworkBaseURL = 'http://localhost/';
    // @phpstan-ignore-next-line
    \CRM_Core_Config::singleton()->cleanURL = 1;
    $this->setUserPermissions([Permissions::ACCESS_CIVICRM, Permissions::ACCESS_FUNDING]);
  }

  protected function tearDown(): void {
    parent::tearDown();
    $this->clearCache();
  }

  protected function clearCache(): void {
    // @phpstan-ignore-next-line
    \Civi::service(FundingCaseManager::class)->clearCache();
    // @phpstan-ignore-next-line
    \Civi::service(FundingCaseTypeManager::class)->clearCache();
    // @phpstan-ignore-next-line
    \Civi::service(FundingProgramManager::class)->clearCache();
    FundingCasePermissionsCache::delete(FALSE)
      ->addWhere('id', 'IS NOT NULL')
      ->execute();
  }

  /**
   * @phpstan-param array<string>|null $permissions
   */
  protected function setUserPermissions(?array $permissions): void {
    $userPermissions = \CRM_Core_Config::singleton()->userPermissionClass;
    // @phpstan-ignore-next-line
    $userPermissions->permissions = $permissions;
  }

}
