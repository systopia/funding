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

namespace Civi\Funding\Api4\Action\RemoteFundingClearingProcess;

use Civi\Api4\RemoteFundingClearingProcess;
use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\Traits\ClearingProcessFixturesTrait;

/**
 * @covers \Civi\Api4\RemoteFundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\Remote\FundingClearingProcess\GetFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\RemoteGetFormActionHandler
 *
 * @group headless
 */
final class GetActionTest extends AbstractRemoteFundingHeadlessTestCase {

  use ClearingProcessFixturesTrait;

  private string $remoteContactId;

  protected function setUp(): void {
    parent::setUp();

    $this->addFixtures(['status' => 'draft', 'report_data' => ['foo' => 'bar']]);
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::CLEARING_MODIFY],
    );

    $this->remoteContactId = (string) $contact['id'];
  }

  public function test(): void {
    static::assertSame(
      [$this->clearingProcessBundle->getClearingProcess()->toArray()],
      RemoteFundingClearingProcess::get()->setRemoteContactId($this->remoteContactId)->execute()->getArrayCopy()
    );

    static::assertSame([
      [
        'id' => $this->clearingProcessBundle->getClearingProcess()->getId(),
        'amount_recorded_costs' => 0.0,
        'amount_recorded_resources' => 0.0,
        'amount_admitted_costs' => 0.0,
        'amount_admitted_resources' => 0.0,
      ],
    ], RemoteFundingClearingProcess::get()
      ->setRemoteContactId($this->remoteContactId)
      ->addSelect(
        'amount_recorded_costs',
        'amount_recorded_resources',
        'amount_admitted_costs',
        'amount_admitted_resources',
      )->execute()->getArrayCopy()
    );

    $this->clearCache();
    $contactNotPermitted = ContactFixture::addIndividual();
    static::assertCount(0, RemoteFundingClearingProcess::get()
      ->setRemoteContactId((string) $contactNotPermitted['id'])
      ->execute()
    );
  }

}
