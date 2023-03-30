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

use Civi\Funding\Api4\Permissions;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandler;
use Civi\Funding\FundingCase\FundingCaseManager;
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
  }

  public static function tearDownAfterClass(): void {
    parent::tearDownAfterClass();
    ClockMock::withClockMock(FALSE);
  }

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      // Required for managed entities to be available
      ->install('activity-entity')
      ->install('de.systopia.identitytracker')
      ->install('de.systopia.remotetools')
      ->install('org.civicrm.search_kit')
      ->install('de.systopia.civioffice')
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

  /**
   * @phpstan-param array<string>|null $permissions
   */
  protected function setUserPermissions(?array $permissions): void {
    $userPermissions = \CRM_Core_Config::singleton()->userPermissionClass;
    // @phpstan-ignore-next-line
    $userPermissions->permissions = $permissions;
  }

}
