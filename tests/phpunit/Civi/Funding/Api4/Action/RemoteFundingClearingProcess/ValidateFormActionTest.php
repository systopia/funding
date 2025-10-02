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
 * @covers \Civi\Funding\Api4\Action\Remote\FundingClearingProcess\ValidateFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\RemoteValidateFormActionHandler
 *
 * @group headless
 */
final class ValidateFormActionTest extends AbstractRemoteFundingHeadlessTestCase {

  use ClearingProcessFixturesTrait;

  private string $remoteContactId;

  protected function setUp(): void {
    parent::setUp();

    $this->addFixtures(['status' => 'draft']);
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::CLEARING_MODIFY],
    );

    $this->remoteContactId = (string) $contact['id'];
  }

  public function testInvalid(): void {
    $result = RemoteFundingClearingProcess::validateForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                'receiptNumber' => 'A123',
                'receiptDate' => '2024-04-03',
                'paymentDate' => '2024-04-04',
                'paymentParty' => 'Payee',
                'reason' => 'costTest',
                'amount' => 'abc',
                'amountAdmitted' => NULL,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                'receiptNumber' => 'A123',
                'receiptDate' => '2024-04-03',
                'paymentDate' => '2024-04-04',
                'paymentParty' => 'Payer',
                'reason' => 'resourceTest',
                'amount' => 'abc',
                'amountAdmitted' => NULL,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 123],
        '_action' => 'invalid',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertFalse($result['valid']);
    static::assertCount(4, $result['errors']);
    static::assertIsArray($result['data']);
  }

  public function testValid(): void {
    $result = RemoteFundingClearingProcess::validateForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                'receiptNumber' => 'A123',
                'receiptDate' => '2024-04-03',
                'paymentDate' => '2024-04-04',
                'paymentParty' => 'Payee',
                'reason' => 'costTest',
                'amount' => 2,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                'receiptNumber' => 'A123',
                'receiptDate' => '2024-04-03',
                'paymentDate' => '2024-04-04',
                'paymentParty' => 'Payer',
                'reason' => 'resourcesTest',
                'amount' => 3,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'save',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertTrue($result['valid']);
    static::assertEquals(new \stdClass(), $result['errors']);
    static::assertIsArray($result['data']);
  }

}
