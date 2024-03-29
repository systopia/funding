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

/**
 * Collection of upgrade steps.
 */
final class CRM_Funding_Upgrader extends CRM_Extension_Upgrader_Base {

  public function upgrade_0001(): bool {
    $this->ctx->log->info('Applying database migration 0001');
    $this->executeSqlFile('sql/upgrade/0001.sql');

    return TRUE;
  }

}
