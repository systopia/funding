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

use Civi\Api4\FundingClearingCostItem;
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\FundingClearingResourcesItem;
use Civi\Api4\RemoteFundingClearingProcess;
use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Fixtures\ClearingCostItemFixture;
use Civi\Funding\Fixtures\ClearingResourcesItemFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\Traits\ClearingProcessFixturesTrait;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\PHPUnit\Traits\ArrayAssertTrait;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Api4\RemoteFundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\Remote\FundingClearingProcess\SubmitFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\RemoteSubmitFormActionHandler
 *
 * @group headless
 */
final class SubmitFormActionTest extends AbstractRemoteFundingHeadlessTestCase {

  use ClearingProcessFixturesTrait;

  use ArrayAssertTrait;

  use ArraySubsetAsserts;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(123456);
  }

  private string $remoteContactId;

  protected function setUp(): void {
    parent::setUp();

    $this->addFixtures([
      'status' => 'draft',
      'creation_date' => date('Y-m-d H:i:s', time() - 1),
      'modification_date' => date('Y-m-d H:i:s'),
    ]);
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::CLEARING_MODIFY],
    );

    $this->remoteContactId = (string) $contact['id'];
  }

  public function testInvalid(): void {

    $result = RemoteFundingClearingProcess::submitForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_financePlanItemId' => $this->costItem->getId(),
                'receiptNumber' => 'A123',
                'receiptDate' => '2024-04-03',
                'paymentDate' => '2024-04-04',
                'paymentParty' => 'Payee',
                'reason' => 'costTest',
                'amount' => 'abc',
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                '_financePlanItemId' => $this->resourcesItem->getId(),
                'receiptNumber' => 'A123',
                'receiptDate' => '2024-04-03',
                'paymentDate' => '2024-04-04',
                'paymentParty' => 'Payer',
                'reason' => 'resourcesTest',
                'amount' => 'abc',
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 123],
        '_action' => 'invalid',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertSame(RemoteSubmitResponseActions::SHOW_VALIDATION, $result['action']);
    static::assertSame('Validation failed', $result['message']);
    static::assertIsArray($result['errors']);
    static::assertCount(1, $result['errors']);
    static::assertEquals(new \stdClass(), $result['files']);
  }

  public function testValid(): void {
    $result = RemoteFundingClearingProcess::submitForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_financePlanItemId' => $this->costItem->getId(),
                'receiptNumber' => 'A123',
                'receiptDate' => '2024-04-03',
                'paymentDate' => '2024-04-04',
                'paymentParty' => 'Payee',
                'reason' => 'costTest',
                'amount' => 2,
                // should be ignored.
                'amountAdmitted' => 4,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                '_financePlanItemId' => $this->resourcesItem->getId(),
                'receiptNumber' => 'A123',
                'receiptDate' => '2024-04-03',
                'paymentDate' => '2024-04-04',
                'paymentParty' => 'Payer',
                'reason' => 'resourcesTest',
                'amount' => 3,
                // should be ignored.
                'amountAdmitted' => 5,
              ],
            ],
          ],
        ],
        'reportData' => [
          'foo' => 'bar',
          'file' => 'https://example.org/test.txt',
        ],
        '_action' => 'save',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertSame(RemoteSubmitResponseActions::RELOAD_FORM, $result['action']);
    static::assertSame('Saved', $result['message']);
    static::assertArrayNotHasKey('errors', $result);

    static::assertIsArray($result['files']);
    static::assertArrayHasSameKeys(['https://example.org/test.txt'], $result['files']);
    static::assertIsString($result['files']['https://example.org/test.txt']);
    static::assertStringStartsWith('http://localhost/', $result['files']['https://example.org/test.txt']);

    static::assertArraySubset([
      'clearing_process_id' => $this->clearingProcessBundle->getClearingProcess()->getId(),
      'application_cost_item_id' => $this->costItem->getId(),
      'status' => 'new',
      'file_id' => NULL,
      'receipt_number' => 'A123',
      'receipt_date' => '2024-04-03',
      'payment_date' => '2024-04-04',
      'payment_party' => 'Payee',
      'reason' => 'costTest',
      'amount' => 2.0,
      'amount_admitted' => NULL,
      'properties' => NULL,
      'form_key' => $this->costItem->getId() . '/0',
    ], FundingClearingCostItem::get(FALSE)->execute()->single());

    static::assertArraySubset([
      'clearing_process_id' => $this->clearingProcessBundle->getClearingProcess()->getId(),
      'app_resources_item_id' => $this->resourcesItem->getId(),
      'status' => 'new',
      'file_id' => NULL,
      'receipt_number' => 'A123',
      'receipt_date' => '2024-04-03',
      'payment_date' => '2024-04-04',
      'payment_party' => 'Payer',
      'reason' => 'resourcesTest',
      'amount' => 3.0,
      'amount_admitted' => NULL,
      'properties' => NULL,
      'form_key' => $this->resourcesItem->getId() . '/0',
    ], FundingClearingResourcesItem::get(FALSE)->execute()->single());
  }

  public function testStatusAndAmountIsReset(): void {
    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId(),
      ['status' => 'accepted', 'amount' => 5, 'amount_admitted' => 4]
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId(),
      ['status' => 'rejected', 'amount' => 3, 'amount_admitted' => 0]
    );

    $result = RemoteFundingClearingProcess::submitForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_id' => $clearingCostItem->getId(),
                '_financePlanItemId' => $this->costItem->getId(),
                'receiptNumber' => 'A123',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'new payee',
                'reason' => 'new cost reason',
                'amount' => 2,
                // should be ignored.
                'amountAdmitted' => 4,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                '_id' => $clearingResourcesItem->getId(),
                '_financePlanItemId' => $this->resourcesItem->getId(),
                'receiptNumber' => 'A123',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'new payer',
                'reason' => 'new resources reason',
                'amount' => 3,
                // should be ignored.
                'amountAdmitted' => 0,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'save',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertSame(RemoteSubmitResponseActions::RELOAD_FORM, $result['action']);
    static::assertSame('Saved', $result['message']);
    static::assertArrayNotHasKey('errors', $result);
    static::assertEquals(new \stdClass(), $result['files']);

    static::assertEquals([
      'id' => $clearingCostItem->getId(),
      'clearing_process_id' => $this->clearingProcessBundle->getClearingProcess()->getId(),
      'application_cost_item_id' => $this->costItem->getId(),
      'status' => 'new',
      'file_id' => NULL,
      'receipt_number' => 'A123',
      'receipt_date' => '2000-12-31',
      'payment_date' => '2001-01-01',
      'payment_party' => 'new payee',
      'reason' => 'new cost reason',
      'amount' => 2.0,
      'amount_admitted' => NULL,
      'properties' => NULL,
      'form_key' => $this->costItem->getId() . '/0',
    ], FundingClearingCostItem::get(FALSE)->execute()->single());

    static::assertEquals([
      'id' => $clearingResourcesItem->getId(),
      'clearing_process_id' => $this->clearingProcessBundle->getClearingProcess()->getId(),
      'app_resources_item_id' => $this->resourcesItem->getId(),
      'status' => 'new',
      'file_id' => NULL,
      'receipt_number' => 'A123',
      'receipt_date' => '2000-12-31',
      'payment_date' => '2001-01-01',
      'payment_party' => 'new payer',
      'reason' => 'new resources reason',
      'amount' => 3.0,
      'amount_admitted' => NULL,
      'properties' => NULL,
      'form_key' => $this->resourcesItem->getId() . '/0',
    ], FundingClearingResourcesItem::get(FALSE)->execute()->single());
  }

  public function testStatusAndAmountIsNotResetIfClearingItemUnchanged(): void {
    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId(),
      ['status' => 'accepted', 'amount' => 5, 'amount_admitted' => 4.1]
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId(),
      ['status' => 'rejected', 'amount' => 3, 'amount_admitted' => 0.1]
    );

    $result = RemoteFundingClearingProcess::submitForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_id' => $clearingCostItem->getId(),
                '_financePlanItemId' => $this->costItem->getId(),
                'receiptNumber' => $clearingCostItem->getReceiptNumber(),
                'receiptDate' => $clearingCostItem->getReceiptDate()?->format('Y-m-d'),
                'paymentDate' => $clearingCostItem->getPaymentDate()?->format('Y-m-d'),
                'paymentParty' => $clearingCostItem->getPaymentParty(),
                'reason' => $clearingCostItem->getReason(),
                'amount' => $clearingCostItem->getAmount(),
                'amountAdmitted' => $clearingCostItem->getAmountAdmitted(),
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                '_id' => $clearingResourcesItem->getId(),
                '_financePlanItemId' => $this->resourcesItem->getId(),
                'receiptNumber' => $clearingResourcesItem->getReceiptNumber(),
                'receiptDate' => $clearingResourcesItem->getReceiptDate()?->format('Y-m-d'),
                'paymentDate' => $clearingResourcesItem->getPaymentDate()?->format('Y-m-d'),
                'paymentParty' => $clearingResourcesItem->getPaymentParty(),
                'reason' => $clearingResourcesItem->getReason(),
                'amount' => $clearingResourcesItem->getAmount(),
                'amountAdmitted' => $clearingResourcesItem->getAmountAdmitted(),
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'save',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertSame(RemoteSubmitResponseActions::RELOAD_FORM, $result['action']);
    static::assertSame('Saved', $result['message']);
    static::assertArrayNotHasKey('errors', $result);
    static::assertEquals(new \stdClass(), $result['files']);

    static::assertEquals(
      $clearingCostItem->toArray(),
      FundingClearingCostItem::get(FALSE)->execute()->single()
    );
    static::assertEquals(
      $clearingResourcesItem->toArray(),
      FundingClearingResourcesItem::get(FALSE)->execute()->single()
    );
  }

  public function testModifyDoesNotChangeData(): void {
    $clearingProcess = $this->clearingProcessBundle->getClearingProcess();
    $clearingProcess->setStatus('review-requested');
    FundingClearingProcess::update(FALSE)->setValues($clearingProcess->toArray())->execute();

    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId()
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId()
    );

    $result = RemoteFundingClearingProcess::submitForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_id' => $clearingCostItem->getId(),
                '_financePlanItemId' => $this->costItem->getId(),
                'receiptNumber' => 'ignored',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'ignored',
                'reason' => 'ignored',
                'amount' => 2,
                'amountAdmitted' => 0,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                '_id' => $clearingResourcesItem->getId(),
                '_financePlanItemId' => $this->resourcesItem->getId(),
                'receiptNumber' => 'ignored',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'ignored',
                'reason' => 'ignored',
                'amount' => 3,
                'amountAdmitted' => 2.3,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'modify',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertArrayNotHasKey('errors', $result);

    static::assertEquals($clearingCostItem->toArray(), FundingClearingCostItem::get(FALSE)->execute()->single());
    static::assertEquals(
      $clearingResourcesItem->toArray(),
      FundingClearingResourcesItem::get(FALSE)->execute()->single()
    );

    static::assertEquals(
      ['status' => 'draft'] + $this->clearingProcessBundle->getClearingProcess()->toArray(),
      FundingClearingProcess::get(FALSE)->execute()->single()
    );
  }

}
