<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

use Civi\Api4\FundingApplicationResourcesItem;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ApplicationProcessBundleFixture;
use Civi\Funding\FundingCaseTypes\AuL\SammelantragKurs\KursConstants;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0003
 *
 * @group headless
 */
final class Upgrader0003Test extends AbstractFundingHeadlessTestCase {

  public function testExecute(): void {
    /** @var \Civi\Funding\Upgrade\Upgrader0003 $upgrader */
    $upgrader = \Civi::service(Upgrader0003::class);

    $requestData = [
      'grunddaten' => [
        'titel' => 'Test',
        'kurzbeschreibungDerInhalte' => 'foo bar',
        'zeitraeume' => [
          [
            'beginn' => '2022-08-24',
            'ende' => '2022-08-24',
          ],
        ],
        'teilnehmer' => [
          'gesamt' => 20,
          'referenten' => 1,
        ],
      ],
      'zuschuss' => [
        'teilnehmerkosten' => 10,
        'fahrtkosten' => 20,
        'honorarkosten' => 30,
      ],
      'beschreibung' => [
        'ziele' => [
          'persoenlichkeitsbildung',
        ],
        'bildungsanteil' => 22,
        'veranstaltungsort' => 'Veranstaltungsort',
      ],
    ];

    ApplicationProcessBundleFixture::create(
      ['request_data' => $requestData],
      [],
      ['name' => KursConstants::FUNDING_CASE_TYPE_NAME]
    );

    $log = new \Log_null('test');
    $upgrader->execute($log);

    $resourcesItems = FundingApplicationResourcesItem::get(FALSE)->execute();
    static::assertCount(5, $resourcesItems);
  }

}
