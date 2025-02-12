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

use Civi\Core\Exception\DBQueryException;
use Civi\Funding\Upgrade\Upgrader0002;
use Civi\Funding\Upgrade\Upgrader0003;
use Civi\Funding\Upgrade\Upgrader0006;
use Civi\Funding\Upgrade\Upgrader0008;
use Civi\Funding\Upgrade\Upgrader0009;
use Civi\Funding\Upgrade\Upgrader0010;
use Civi\Funding\Upgrade\Upgrader0011;
use CRM_Funding_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
final class CRM_Funding_Upgrader extends CRM_Extension_Upgrader_Base {

  public function install(): void {
    $this->installJsonOverlapsSqlFunction();
  }

  public function upgrade_0001(): bool {
    $this->ctx->log->info('Applying database migration 0001');
    $this->executeSqlFile('sql/upgrade/0001.sql');

    return TRUE;
  }

  public function upgrade_0002(): bool {
    $this->ctx->log->info('Applying database migration 0002');
    $this->executeSqlFile('sql/upgrade/0002.sql');
    /** @var \Civi\Funding\Upgrade\Upgrader0002 $upgrader */
    $upgrader = \Civi::service(Upgrader0002::class);

    $upgrader->execute($this->ctx->log);

    return TRUE;
  }

  public function upgrade_0003(): bool {
    /** @var \Civi\Funding\Upgrade\Upgrader0003 $upgrader */
    $upgrader = \Civi::service(Upgrader0003::class);

    $upgrader->execute($this->ctx->log);

    return TRUE;
  }

  public function upgrade_0004(): bool {
    $this->ctx->log->info('Applying database migration 0004');
    $this->executeSqlFile('sql/upgrade/0004.sql');

    return TRUE;
  }

  public function upgrade_0005(): bool {
    $this->ctx->log->info('Applying database migration 0005');
    $this->executeSqlFile('sql/upgrade/0005.sql');

    return TRUE;
  }

  public function upgrade_0006(): bool {
    /** @var \Civi\Funding\Upgrade\Upgrader0006 $upgrader */
    $upgrader = \Civi::service(Upgrader0006::class);
    $upgrader->execute($this->ctx->log);

    return TRUE;
  }

  public function upgrade_0007(): bool {
    $this->ctx->log->info('Applying database migration 0007');
    $this->executeSqlFile('sql/upgrade/0007.sql');

    return TRUE;
  }

  public function upgrade_0008(): bool {
    /** @var \Civi\Funding\Upgrade\Upgrader0008 $upgrader */
    $upgrader = \Civi::service(Upgrader0008::class);
    $upgrader->execute($this->ctx->log);

    return TRUE;
  }

  public function upgrade_0009(): bool {
    /** @var \Civi\Funding\Upgrade\Upgrader0009 $upgrader */
    $upgrader = \Civi::service(Upgrader0009::class);
    $upgrader->execute($this->ctx->log);

    return TRUE;
  }

  public function upgrade_0010(): bool {
    $this->ctx->log->info('Applying database migration 0010');
    $this->executeSqlFile('sql/upgrade/0010.sql');

    /** @var \Civi\Funding\Upgrade\Upgrader0010 $upgrader */
    $upgrader = \Civi::service(Upgrader0010::class);
    $upgrader->execute($this->ctx->log);

    return TRUE;
  }

  public function upgrade_0011(): bool {
    $this->ctx->log->info('Applying database migration 0011');
    $this->executeSqlFile('sql/upgrade/0011.sql');

    $this->ctx->log->info('Installing JSON overlaps SQL function');
    $this->installJsonOverlapsSqlFunction();

    /** @var \Civi\Funding\Upgrade\Upgrader0010 $upgrader */
    $upgrader = \Civi::service(Upgrader0010::class);
    $upgrader->execute($this->ctx->log);

    return TRUE;
  }

  private function installJsonOverlapsSqlFunction(): void {
    try {
      CRM_Core_DAO::executeQuery('JSON_OVERLAPS(NULL, NULL)');
      // Native JSON_OVERLAPS exists (MariaDB >=10.9)
      $this->executeFullSqlFile('sql/functions/funding_json_overlaps_alias.sql');
    }
    catch (DBQueryException $e) {
      // Native JSON_OVERLAPS doesn't exist (MariaDB <10.9)
      $this->executeFullSqlFile('sql/functions/funding_json_overlaps.sql');
    }
  }

  /**
   * executeSqlFile() splits the file contents at ';'. When creating functions
   * this would lead to an invalid SQL statement.
   *
   * @throws \Civi\Core\Exception\DBQueryException
   *
   * @see executeSqlFile()
   */
  private function executeFullSqlFile(string $path): void {
    $sql = file_get_contents(E::path($path));
    assert(is_string($sql));
    CRM_Core_DAO::executeQuery($sql);
  }

}
