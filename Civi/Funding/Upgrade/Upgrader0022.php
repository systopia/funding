<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Upgrade;

final class Upgrader0022 implements UpgraderInterface {

  public function execute(\Log $log): void {
    $log->info('Set default value for funding_renderer_uri');
    if (\Civi::settings()->get('funding_renderer_uri') === NULL) {
      \Civi::settings()->set('funding_renderer_uri', 'unoconv-local-1');
    }
  }

}
