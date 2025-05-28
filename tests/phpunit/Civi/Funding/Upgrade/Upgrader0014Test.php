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

namespace Civi\Funding\Upgrade;

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0014
 *
 * @group headless
 */
final class Upgrader0014Test extends AbstractFundingHeadlessTestCase {

  public function testExecute(): void {
    $clearingProcessBundle = ClearingProcessBundleFixture::create([
      'report_data' => [
        'grunddaten' => [
          'zeitraeume' => [
            [
              'beginn' => '2024-03-04',
              'ende' => '2024-03-05',
            ],
            [
              'beginn' => '2024-04-04',
              'ende' => '2024-04-05',
            ],
          ],
        ],
      ],
    ]);

    /** @var \Civi\Funding\Upgrade\Upgrader0014 $upgrader */
    $upgrader = \Civi::service(Upgrader0014::class);

    // Should migrate contact relations of type 'ContactTypeRelationship'.
    $upgrader->execute(new \Log_null('test'));

    $clearingProcess = FundingClearingProcess::get(FALSE)
      ->addSelect('start_date', 'end_date')
      ->addWhere('id', '=', $clearingProcessBundle->getClearingProcess()->getId())
      ->execute()
      ->single();

    static::assertSame('2024-03-04 00:00:00', $clearingProcess['start_date']);
    static::assertSame('2024-04-05 00:00:00', $clearingProcess['end_date']);
  }

}
