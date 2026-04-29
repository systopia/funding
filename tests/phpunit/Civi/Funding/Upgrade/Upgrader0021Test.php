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

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\AVK1Constants;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0021
 *
 * @group headless
 */
final class Upgrader0021Test extends AbstractFundingHeadlessTestCase {

  public function testExecute(): void {
    $clearingProcessBundle = ClearingProcessBundleFixture::create(
      [
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
      ],
      fundingCaseTypeValues: ['name' => AVK1Constants::FUNDING_CASE_TYPE_NAME],
    );

    /** @var \Civi\Funding\Upgrade\Upgrader0021 $upgrader */
    $upgrader = \Civi::service(Upgrader0021::class);

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
