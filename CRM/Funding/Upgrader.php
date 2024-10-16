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

use Civi\Funding\Upgrade\Upgrader0002;
use Civi\Funding\Upgrade\Upgrader0003;
use Civi\Funding\Upgrade\Upgrader0006;

/**
 * Collection of upgrade steps.
 */
final class CRM_Funding_Upgrader extends CRM_Extension_Upgrader_Base {

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

}
