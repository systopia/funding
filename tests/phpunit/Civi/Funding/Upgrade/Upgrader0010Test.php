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

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingApplicationSnapshot;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ApplicationProcessBundleFixture;
use Civi\Funding\Fixtures\ApplicationSnapshotFixture;

/**
 * @covers \Civi\Funding\Upgrade\Upgrader0010
 *
 * @group headless
 */
final class Upgrader0010Test extends AbstractFundingHeadlessTestCase {

  public function testExecute(): void {
    $applicationProcessBundle = ApplicationProcessBundleFixture::create([
      // This combination makes no sense, but it's just to check if the values are changed.
      'status' => 'withdrawn',
      'is_in_work' => TRUE,
      'is_rejected' => TRUE,
      'is_withdrawn' => FALSE,
    ]);

    $applicationSnapshot = ApplicationSnapshotFixture::addFixture(
      $applicationProcessBundle->getApplicationProcess()->getId(),
      [
        // This combination makes no sense, but it's just to check if the values are changed.
        'status' => 'draft',
        'is_in_work' => FALSE,
        'is_rejected' => TRUE,
        'is_withdrawn' => TRUE,
      ]
    );

    /** @var \Civi\Funding\Upgrade\Upgrader0010 $upgrader */
    $upgrader = \Civi::service(Upgrader0010::class);
    $upgrader->execute(new \Log_null('test'));

    $applicationProcessValues = FundingApplicationProcess::get(FALSE)
      ->addSelect('is_in_work', 'is_rejected', 'is_withdrawn')
      ->addWhere('id', '=', $applicationProcessBundle->getApplicationProcess()->getId())
      ->execute()
      ->single();
    static::assertFalse($applicationProcessValues['is_in_work']);
    static::assertFalse($applicationProcessValues['is_rejected']);
    static::assertTrue($applicationProcessValues['is_withdrawn']);

    $applicationSnapshotValues = FundingApplicationSnapshot::get(FALSE)
      ->addSelect('is_in_work', 'is_rejected', 'is_withdrawn')
      ->addWhere('id', '=', $applicationSnapshot->getId())
      ->execute()
      ->single();
    static::assertTrue($applicationSnapshotValues['is_in_work']);
    static::assertFalse($applicationSnapshotValues['is_rejected']);
    static::assertFalse($applicationSnapshotValues['is_withdrawn']);
  }

}
