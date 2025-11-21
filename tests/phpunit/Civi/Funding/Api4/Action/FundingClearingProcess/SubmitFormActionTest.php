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

namespace Civi\Funding\Api4\Action\FundingClearingProcess;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingClearingCostItem;
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\FundingClearingResourcesItem;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Fixtures\ClearingCostItemFixture;
use Civi\Funding\Fixtures\ClearingResourcesItemFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\Traits\ClearingProcessFixturesTrait;
use Civi\Funding\Util\RequestTestUtil;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Api4\FundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\FundingClearingProcess\SubmitFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\SubmitFormActionHandler
 * @covers \Civi\Funding\ClearingProcess\Handler\ClearingFormSubmitHandler
 *
 * @group headless
 */
final class SubmitFormActionTest extends AbstractFundingHeadlessTestCase {

  use ClearingProcessFixturesTrait;

  private int $contactId;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(123456);
  }

  protected function setUp(): void {
    parent::setUp();

    $this->addFixtures([
      'status' => 'review',
      'creation_date' => date('Y-m-d H:i:s', time() - 1),
      'modification_date' => date('Y-m-d H:i:s'),
    ]);
    $contact = ContactFixture::addIndividual();
    $this->contactId = $contact['id'];

    RequestTestUtil::mockInternalRequest($contact['id']);
  }

  public function testInvalid(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [
        ClearingProcessPermissions::REVIEW_CONTENT,
        ClearingProcessPermissions::REVIEW_CALCULATIVE,
        ClearingProcessPermissions::REVIEW_AMEND,
      ],
    );

    $result = FundingClearingProcess::submitForm()
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
                'amountAdmitted' => 1.2,
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
                'amount' => 'abc',
                'amountAdmitted' => 2.3,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 123],
        '_action' => 'invalid',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertIsArray($result['errors']);
    static::assertCount(1, $result['errors']);
    static::assertIsArray($result['data']);
    static::assertEquals(new \stdClass(), $result['files']);
  }

  public function testUpdate(): void {
    // Without review calculative permission, the admitted value is ignored.
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_AMEND],
    );

    $result = FundingClearingProcess::submitForm()
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
                'amountAdmitted' => 1.2,
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
                'amountAdmitted' => 0,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'update',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertEquals(new \stdClass(), $result['errors']);
    static::assertIsArray($result['data']);
    static::assertIsInt($result['data']['costItems'][$this->costItem->getId()]['records'][0]['_id']);
    static::assertIsInt($result['data']['resourcesItems'][$this->resourcesItem->getId()]['records'][0]['_id']);
    static::assertEquals(new \stdClass(), $result['files']);

    static::assertEquals([
      'id' => $result['data']['costItems'][$this->costItem->getId()]['records'][0]['_id'],
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

    static::assertEquals([
      'id' => $result['data']['resourcesItems'][$this->resourcesItem->getId()]['records'][0]['_id'],
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

  public function testAddClearingItemWithoutAmendPermission(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CALCULATIVE, ClearingProcessPermissions::REVIEW_CONTENT],
    );

    ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId()
    );

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Permission to add new clearing items is missing');
    FundingClearingProcess::submitForm()
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
                'amountAdmitted' => 1.2,
              ],
            ],
          ],
        ],
        'resourcesItems' => [],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'update',
      ])
      ->execute()
      ->getArrayCopy();
  }

  public function testRemoveClearingItemWithoutAmendPermission(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CALCULATIVE, ClearingProcessPermissions::REVIEW_CONTENT],
    );

    ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId()
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId()
    );

    $result = FundingClearingProcess::submitForm()
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'update',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertIsArray($result['errors']);
    // Validation stops at first error. Thus, there's no error for the resources item.
    static::assertEquals([
      '/costItems/' . $this->costItem->getId() . '/records' => [
        'At least one item is required.',
      ],
    ], $result['errors']);
  }

  public function testUpdateClearingItems(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CALCULATIVE, ClearingProcessPermissions::REVIEW_AMEND],
    );

    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId(),
      ['form_key' => 'costs/0'],
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId(),
      ['form_key' => 'resources/one'],
    );

    $result = FundingClearingProcess::submitForm()
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_id' => $clearingCostItem->getId(),
                'receiptNumber' => 'A123',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'new payee',
                'reason' => 'new cost reason',
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
                'receiptNumber' => 'A123',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'new payer',
                'reason' => 'new resources reason',
                'amount' => 3,
                'amountAdmitted' => 2.3,
                'properties' => NULL,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'update',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertEquals(new \stdClass(), $result['errors']);
    static::assertIsArray($result['data']);
    static::assertIsInt($result['data']['costItems'][$this->costItem->getId()]['records'][0]['_id']);
    static::assertIsInt($result['data']['resourcesItems'][$this->resourcesItem->getId()]['records'][0]['_id']);
    static::assertEquals(new \stdClass(), $result['files']);

    static::assertEquals([
      'id' => $result['data']['costItems'][$this->costItem->getId()]['records'][0]['_id'],
      'clearing_process_id' => $this->clearingProcessBundle->getClearingProcess()->getId(),
      'application_cost_item_id' => $this->costItem->getId(),
      'status' => 'rejected',
      'file_id' => NULL,
      'receipt_number' => 'A123',
      'receipt_date' => '2000-12-31',
      'payment_date' => '2001-01-01',
      'payment_party' => 'new payee',
      'reason' => 'new cost reason',
      'amount' => 2.0,
      'amount_admitted' => 0.0,
      'properties' => NULL,
      'form_key' => $this->costItem->getId() . '/0',
    ], FundingClearingCostItem::get(FALSE)->execute()->single());

    static::assertEquals([
      'id' => $result['data']['resourcesItems'][$this->resourcesItem->getId()]['records'][0]['_id'],
      'clearing_process_id' => $this->clearingProcessBundle->getClearingProcess()->getId(),
      'app_resources_item_id' => $this->resourcesItem->getId(),
      'status' => 'accepted',
      'file_id' => NULL,
      'receipt_number' => 'A123',
      'receipt_date' => '2000-12-31',
      'payment_date' => '2001-01-01',
      'payment_party' => 'new payer',
      'reason' => 'new resources reason',
      'amount' => 3.0,
      'amount_admitted' => 2.3,
      'properties' => NULL,
      'form_key' => $this->resourcesItem->getId() . '/0',
    ], FundingClearingResourcesItem::get(FALSE)->execute()->single());
  }

  public function testAcceptContentDoesNotChangeData(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CONTENT, ClearingProcessPermissions::REVIEW_AMEND],
    );

    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId(),
      ['form_key' => 'costs/0']
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId(),
      ['form_key' => 'resources/one']
    );

    $result = FundingClearingProcess::submitForm()
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
        '_action' => 'accept-content',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertEquals(new \stdClass(), $result['errors']);

    static::assertEquals($clearingCostItem->toArray(), FundingClearingCostItem::get(FALSE)->execute()->single());
    static::assertEquals(
      $clearingResourcesItem->toArray(),
      FundingClearingResourcesItem::get(FALSE)->execute()->single()
    );

    static::assertEquals(
      [
        'is_review_content' => TRUE,
      ] + $this->clearingProcessBundle->getClearingProcess()->toArray(),
      FundingClearingProcess::get(FALSE)->execute()->single()
    );
  }

  public function testAcceptCalculativeDoesNotChangeData(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CALCULATIVE, ClearingProcessPermissions::REVIEW_AMEND],
    );

    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId(),
      ['status' => 'accepted', 'amount_admitted' => 1.1]
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId(),
      ['status' => 'rejected', 'amount_admitted' => 0]
    );

    $result = FundingClearingProcess::submitForm()
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_id' => $clearingCostItem->getId(),
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
        '_action' => 'accept-calculative',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertEquals(new \stdClass(), $result['errors']);

    static::assertEquals($clearingCostItem->toArray(), FundingClearingCostItem::get(FALSE)->execute()->single());
    static::assertEquals(
      $clearingResourcesItem->toArray(),
      FundingClearingResourcesItem::get(FALSE)->execute()->single()
    );

    static::assertEquals(
      [
        'is_review_calculative' => TRUE,
      ] + $this->clearingProcessBundle->getClearingProcess()->toArray(),
      FundingClearingProcess::get(FALSE)->execute()->single()
    );
  }

  public function testAcceptCalculativeDoesNotInitializesAmountAdmittedIfNotAllowed(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CALCULATIVE, ClearingProcessPermissions::REVIEW_AMEND],
    );

    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId(),
      ['status' => 'new', 'amount_admitted' => NULL]
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId(),
      ['status' => 'rejected', 'amount_admitted' => NULL]
    );

    $result = FundingClearingProcess::submitForm()
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_id' => $clearingCostItem->getId(),
                'receiptNumber' => 'ignored',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'ignored',
                'reason' => 'ignored',
                'amount' => 2,
                'amountAdmitted' => NULL,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                '_id' => $clearingResourcesItem->getId(),
                'receiptNumber' => 'ignored',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'ignored',
                'reason' => 'ignored',
                'amount' => 3,
                'amountAdmitted' => NULL,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'accept-calculative',
      ])->execute()->getArrayCopy();
    static::assertSame(['/_action' => ['The value is not allowed.']], $result['errors']);
  }

  public function testAcceptCalculativeDoesNotChangeDataButInitializesAmountAdmittedIfAllowed(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CALCULATIVE, ClearingProcessPermissions::REVIEW_AMEND],
    );

    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId(),
      ['status' => 'new', 'amount_admitted' => NULL]
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId(),
      ['status' => 'rejected', 'amount_admitted' => NULL]
    );

    $submitAction = FundingClearingProcess::submitForm()
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_id' => $clearingCostItem->getId(),
                'receiptNumber' => 'ignored',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'ignored',
                'reason' => 'ignored',
                'amount' => 2,
                'amountAdmitted' => NULL,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                '_id' => $clearingResourcesItem->getId(),
                'receiptNumber' => 'ignored',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'ignored',
                'reason' => 'ignored',
                'amount' => 3.4,
                'amountAdmitted' => NULL,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'accept-calculative',
      ]);

    $this->getTestCaseTypeMetaData()->setGeneralClearingAdmitAllowed(TRUE);
    $result = $submitAction->execute()->getArrayCopy();
    static::assertEquals(new \stdClass(), $result['errors']);

    static::assertEquals(
      ['amount_admitted' => 2.0, 'status' => 'accepted'] + $clearingCostItem->toArray(),
      FundingClearingCostItem::get(FALSE)->execute()->single()
    );
    static::assertEquals(
      ['amount_admitted' => 3.4, 'status' => 'accepted'] + $clearingResourcesItem->toArray(),
      FundingClearingResourcesItem::get(FALSE)->execute()->single()
    );

    static::assertEquals(
      [
        'is_review_calculative' => TRUE,
      ] + $this->clearingProcessBundle->getClearingProcess()->toArray(),
      FundingClearingProcess::get(FALSE)->execute()->single()
    );
  }

  public function testRejectDoesNotChangeDataButSetsAmountAdmittedToZero(): void {
    FundingCaseContactRelationFixture::addContact(
      $this->contactId,
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CALCULATIVE, ClearingProcessPermissions::REVIEW_AMEND],
    );

    $clearingCostItem = ClearingCostItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->costItem->getId(),
      ['status' => 'new', 'amount_admitted' => 1.2]
    );
    $clearingResourcesItem = ClearingResourcesItemFixture::addFixture(
      $this->clearingProcessBundle->getClearingProcess()->getId(),
      $this->resourcesItem->getId(),
      ['status' => 'rejected', 'amount_admitted' => 3.4]
    );

    $submitAction = FundingClearingProcess::submitForm()
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                '_id' => $clearingCostItem->getId(),
                'receiptNumber' => 'ignored',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'ignored',
                'reason' => 'ignored',
                'amount' => 2,
                'amountAdmitted' => 1.2,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                '_id' => $clearingResourcesItem->getId(),
                'receiptNumber' => 'ignored',
                'receiptDate' => '2000-12-31',
                'paymentDate' => '2001-01-01',
                'paymentParty' => 'ignored',
                'reason' => 'ignored',
                'amount' => 3.4,
                'amountAdmitted' => 3.4,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'reject',
      ]);

    $this->getTestCaseTypeMetaData()->setGeneralClearingAdmitAllowed(TRUE);
    $result = $submitAction->execute()->getArrayCopy();
    static::assertEquals(new \stdClass(), $result['errors']);

    static::assertEquals(
      ['amount_admitted' => 0.0, 'status' => 'rejected'] + $clearingCostItem->toArray(),
      FundingClearingCostItem::get(FALSE)->execute()->single()
    );
    static::assertEquals(
      ['amount_admitted' => 0.0, 'status' => 'rejected'] + $clearingResourcesItem->toArray(),
      FundingClearingResourcesItem::get(FALSE)->execute()->single()
    );

    static::assertEquals(
      [
        'status' => 'rejected',
      ] + $this->clearingProcessBundle->getClearingProcess()->toArray(),
      FundingClearingProcess::get(FALSE)->execute()->single()
    );
  }

}
