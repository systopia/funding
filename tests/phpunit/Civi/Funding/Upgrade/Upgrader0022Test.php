<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Upgrade;

use Civi\Funding\AbstractFundingHeadlessTestCase;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0022
 *
 * @group headless
 */
final class Upgrader0022Test extends AbstractFundingHeadlessTestCase {

  public function testExecuteHandlesNullValue(): void {
    \Civi::settings()->set('funding_civioffice_renderer_uri', NULL);

    /** @var \Civi\Funding\Upgrade\Upgrader0022 $upgrader */
    $upgrader = \Civi::service(Upgrader0022::class);

    $upgrader->execute(new \Log_null('test'));

    static::assertSame('unoconv-local', \Civi::settings()->get('funding_civioffice_renderer_uri'));
  }

  public function testExecuteDoesNotOverwriteExisting(): void {
    \Civi::settings()->set('funding_civioffice_renderer_uri', 'custom-renderer');

    /** @var \Civi\Funding\Upgrade\Upgrader0022 $upgrader */
    $upgrader = \Civi::service(Upgrader0022::class);

    $upgrader->execute(new \Log_null('test'));

    static::assertSame('custom-renderer', \Civi::settings()->get('funding_civioffice_renderer_uri'));
  }

}
