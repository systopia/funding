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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\EventSubscriber;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\FundingApplicationCostItem;
use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingClearingCostItem;
use Civi\Api4\FundingProgram;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Permissions;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ClearingCostItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Fixtures\ApplicationCostItemFixture;
use Civi\Funding\Fixtures\ClearingCostItemFixture;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\PersonalkostenMetaData;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\EventSubscriber\PersonalkostenFundingProgramSubscriber
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\PersonalkostenApplicationProcessUpdater
 *
 * @group headless
 */
final class PersonalkostenFundingProgramSubscriberTest extends AbstractFundingHeadlessTestCase {

  private ClearingProcessEntityBundle $clearingProcessBundle;

  private ClearingCostItemEntity $personalkostenClearingCostItem;

  private ApplicationCostItemEntity $personalkostenCostItem;

  private ClearingCostItemEntity $sachkostenpauschaleClearingCostItem;

  private ApplicationCostItemEntity $sachkostenpauschaleCostItem;

  protected function setUp(): void {
    parent::setUp();
    $this->setUserPermissions([Permissions::ADMINISTER_FUNDING]);
  }

  protected function tearDown(): void {
    parent::tearDown();
    unset($this->clearingProcessBundle);
    unset($this->personalkostenCostItem);
    unset($this->personalkostenClearingCostItem);
    unset($this->sachkostenpauschaleCostItem);
    unset($this->sachkostenpauschaleClearingCostItem);
  }

  public function testClearedClearingAccepted(): void {
    $this->doTest(function() {
      $sachkostenpauschale = 200.0;
      $foerderquote = 10;
      $personalkostenBeantragt = 100.0;

      $this->createFixtures(
        sachkostenpauschale: $sachkostenpauschale,
        foerderquote: $foerderquote,
        personalkostenBeantragt: $personalkostenBeantragt,
        clearingProcessStatus: 'accepted',
        fundingCaseStatus: FundingCaseStatus::CLEARED,
      );

      $fundingProgram = $this->clearingProcessBundle->getFundingProgram();
      $applicationProcess = $this->clearingProcessBundle->getApplicationProcess();

      static::assertSame($applicationProcess->getAmountRequested(), $applicationProcess->getAmountEligible());
      static::assertNotNull($this->personalkostenClearingCostItem->getAmountAdmitted());
      static::assertSame('accepted', $this->personalkostenClearingCostItem->getStatus());
      static::assertSame(200.0, $this->sachkostenpauschaleClearingCostItem->getAmountAdmitted());
      static::assertSame('accepted', $this->sachkostenpauschaleClearingCostItem->getStatus());

      FundingProgram::update(FALSE)->addWhere('id', '=', $fundingProgram->getId())->setValues([
        'funding_program_extra.foerderquote' => 20,
        'funding_program_extra.sachkostenpauschale' => 300.0,
      ])->execute();

      // Nothing should be changed.
      static::assertEquals(
        $applicationProcess->toArray(),
        FundingApplicationProcess::get(FALSE)->addWhere('id', '=', $applicationProcess->getId())->execute()->single()
      );

      static::assertEquals(
        $this->sachkostenpauschaleCostItem->toArray(),
        FundingApplicationCostItem::get(FALSE)
          ->addWhere('id', '=', $this->sachkostenpauschaleCostItem->getId())
          ->execute()
          ->single()
      );

      static::assertEquals(
        $this->personalkostenCostItem->toArray(),
        FundingApplicationCostItem::get(FALSE)
          ->addWhere('id', '=', $this->personalkostenCostItem->getId())
          ->execute()
          ->single()
      );

      static::assertEquals(
        $this->sachkostenpauschaleClearingCostItem->toArray(),
        FundingClearingCostItem::get(FALSE)
          ->addWhere('id', '=', $this->sachkostenpauschaleClearingCostItem->getId())
          ->execute()
          ->single()
      );

      static::assertEquals(
        $this->personalkostenClearingCostItem->toArray(),
        FundingClearingCostItem::get(FALSE)
          ->addWhere('id', '=', $this->personalkostenClearingCostItem->getId())
          ->execute()
          ->single()
      );
    });
  }

  public function testOngoingClearingAccepted(): void {
    $this->doTest(function() {
      $personalkostenBeantragt = 100.0;
      $this->createFixtures(
        sachkostenpauschale: 200.0,
        foerderquote: 10,
        personalkostenBeantragt: $personalkostenBeantragt,
        clearingProcessStatus: 'accepted',
      );

      $fundingProgram = $this->clearingProcessBundle->getFundingProgram();
      $applicationProcess = $this->clearingProcessBundle->getApplicationProcess();

      static::assertSame($applicationProcess->getAmountRequested(), $applicationProcess->getAmountEligible());
      static::assertNotNull($this->personalkostenClearingCostItem->getAmountAdmitted());
      static::assertSame('accepted', $this->personalkostenClearingCostItem->getStatus());
      static::assertSame(200.0, $this->sachkostenpauschaleClearingCostItem->getAmountAdmitted());
      static::assertSame('accepted', $this->sachkostenpauschaleClearingCostItem->getStatus());

      $newFoerderquote = 20;
      $newSachkostenpauschale = 300.0;
      $newPersonalkostenEligible = round($newFoerderquote * $personalkostenBeantragt / 100, 2);
      $newBeantragterZuschuss = round($newPersonalkostenEligible + $newSachkostenpauschale, 2);
      FundingProgram::update(FALSE)->addWhere('id', '=', $fundingProgram->getId())->setValues([
        'funding_program_extra.foerderquote' => $newFoerderquote,
        'funding_program_extra.sachkostenpauschale' => $newSachkostenpauschale,
      ])->execute();

      $applicationProcessData =
      FundingApplicationProcess::get(FALSE)->addWhere('id', '=', $applicationProcess->getId())->execute()->single();
      $applicationRequestData = $applicationProcessData['request_data'];
      static::assertSame($newFoerderquote, $applicationRequestData['foerderquote']);
      static::assertSame($newSachkostenpauschale, (float) $applicationRequestData['sachkostenpauschale']);
      static::assertSame($newBeantragterZuschuss, (float) $applicationRequestData['beantragterZuschuss']);
      static::assertSame($newBeantragterZuschuss, $applicationProcessData['amount_requested']);
      static::assertSame($newBeantragterZuschuss, $applicationProcessData['amount_eligible']);

      // Amount of cost item for Sachkostenpauschale should be changed.
      static::assertEquals(
      ['amount' => $newSachkostenpauschale] + $this->sachkostenpauschaleCostItem->toArray(),
      FundingApplicationCostItem::get(FALSE)
        ->addWhere('id', '=', $this->sachkostenpauschaleCostItem->getId())
        ->execute()
        ->single()
      );

      // Cost item for Personalkosten should be unchanged.
      static::assertEquals(
      $this->personalkostenCostItem->toArray(),
      FundingApplicationCostItem::get(FALSE)
        ->addWhere('id', '=', $this->personalkostenCostItem->getId())
        ->execute()
        ->single()
      );

      // Amount and amount admitted of clearing cost item for Sachkostenpauschale should be changed.
      static::assertEquals(
      [
        'amount' => $newSachkostenpauschale,
        'amount_admitted' => $newSachkostenpauschale,
      ] + $this->sachkostenpauschaleClearingCostItem->toArray(),
      FundingClearingCostItem::get(FALSE)
        ->addWhere('id', '=', $this->sachkostenpauschaleClearingCostItem->getId())
        ->execute()
        ->single()
      );

      // Amount admitted of clearing cost item for Personalkosten should be changed.
      static::assertEquals(
      [
        'amount_admitted' => $newPersonalkostenEligible,
      ] + $this->personalkostenClearingCostItem->toArray(),
      FundingClearingCostItem::get(FALSE)
        ->addWhere('id', '=', $this->personalkostenClearingCostItem->getId())
        ->execute()
        ->single()
      );
    });
  }

  public function testOngoingClearingDraft(): void {
    $this->doTest(function() {
      $personalkostenBeantragt = 100.0;
      $this->createFixtures(
        sachkostenpauschale: 200.0,
        foerderquote: 10,
        personalkostenBeantragt: $personalkostenBeantragt,
        clearingProcessStatus: 'draft',
      );

      $fundingProgram = $this->clearingProcessBundle->getFundingProgram();
      $applicationProcess = $this->clearingProcessBundle->getApplicationProcess();

      static::assertSame($applicationProcess->getAmountRequested(), $applicationProcess->getAmountEligible());
      static::assertNull($this->personalkostenClearingCostItem->getAmountAdmitted());
      static::assertSame('new', $this->personalkostenClearingCostItem->getStatus());
      static::assertNull($this->sachkostenpauschaleClearingCostItem->getAmountAdmitted());
      static::assertSame('new', $this->sachkostenpauschaleClearingCostItem->getStatus());

      $newFoerderquote = 20;
      $newSachkostenpauschale = 300.0;
      $newPersonalkostenEligible = round($newFoerderquote * $personalkostenBeantragt / 100, 2);
      $newBeantragterZuschuss = round($newPersonalkostenEligible + $newSachkostenpauschale, 2);
      FundingProgram::update(FALSE)->addWhere('id', '=', $fundingProgram->getId())->setValues([
        'funding_program_extra.foerderquote' => $newFoerderquote,
        'funding_program_extra.sachkostenpauschale' => $newSachkostenpauschale,
      ])->execute();

      $applicationProcessData =
        FundingApplicationProcess::get(FALSE)->addWhere('id', '=', $applicationProcess->getId())->execute()->single();
      $applicationRequestData = $applicationProcessData['request_data'];
      static::assertSame($newFoerderquote, $applicationRequestData['foerderquote']);
      static::assertSame($newSachkostenpauschale, (float) $applicationRequestData['sachkostenpauschale']);
      static::assertSame($newBeantragterZuschuss, (float) $applicationRequestData['beantragterZuschuss']);
      static::assertSame($newBeantragterZuschuss, $applicationProcessData['amount_requested']);
      static::assertSame($newBeantragterZuschuss, $applicationProcessData['amount_eligible']);

      // Amount of cost item for Sachkostenpauschale should be changed.
      static::assertEquals(
        ['amount' => $newSachkostenpauschale] + $this->sachkostenpauschaleCostItem->toArray(),
        FundingApplicationCostItem::get(FALSE)
          ->addWhere('id', '=', $this->sachkostenpauschaleCostItem->getId())
          ->execute()
          ->single()
      );

      // Cost item for Personalkosten should be unchanged.
      static::assertEquals(
        $this->personalkostenCostItem->toArray(),
        FundingApplicationCostItem::get(FALSE)
          ->addWhere('id', '=', $this->personalkostenCostItem->getId())
          ->execute()
          ->single()
      );

      // Amount of clearing cost item for Sachkostenpauschale should be changed.
      static::assertEquals(
        [
          'amount' => $newSachkostenpauschale,
        ] + $this->sachkostenpauschaleClearingCostItem->toArray(),
        FundingClearingCostItem::get(FALSE)
          ->addWhere('id', '=', $this->sachkostenpauschaleClearingCostItem->getId())
          ->execute()
          ->single()
      );

      // Clearing cost item for Personalkosten should be unchanged.
      static::assertEquals(
        $this->personalkostenClearingCostItem->toArray(),
        FundingClearingCostItem::get(FALSE)
          ->addWhere('id', '=', $this->personalkostenClearingCostItem->getId())
          ->execute()
          ->single()
      );
    });
  }

  public function testOpenClearingNotStarted(): void {
    $this->doTest(function() {
      $personalkostenBeantragt = 100.0;
      $this->createFixtures(
        sachkostenpauschale: 200.0,
        foerderquote: 10,
        personalkostenBeantragt: $personalkostenBeantragt,
        clearingProcessStatus: 'not-started',
        applicationProcessStatus: 'draft',
        fundingCaseStatus: 'open',
      );

      $fundingProgram = $this->clearingProcessBundle->getFundingProgram();
      $applicationProcess = $this->clearingProcessBundle->getApplicationProcess();

      static::assertSame(0.0, $applicationProcess->getAmountEligible());
      static::assertFalse(isset($this->personalkostenClearingCostItem));
      static::assertFalse(isset($this->sachkostenpauschaleClearingCostItem));

      $newFoerderquote = 20;
      $newSachkostenpauschale = 300.0;
      $newPersonalkostenEligible = round($newFoerderquote * $personalkostenBeantragt / 100, 2);
      $newBeantragterZuschuss = round($newPersonalkostenEligible + $newSachkostenpauschale, 2);
      FundingProgram::update(FALSE)->addWhere('id', '=', $fundingProgram->getId())->setValues([
        'funding_program_extra.foerderquote' => $newFoerderquote,
        'funding_program_extra.sachkostenpauschale' => $newSachkostenpauschale,
      ])->execute();

      $applicationProcessData =
        FundingApplicationProcess::get(FALSE)->addWhere('id', '=', $applicationProcess->getId())->execute()->single();
      $applicationRequestData = $applicationProcessData['request_data'];
      static::assertSame($newFoerderquote, $applicationRequestData['foerderquote']);
      static::assertSame($newSachkostenpauschale, (float) $applicationRequestData['sachkostenpauschale']);
      static::assertSame($newBeantragterZuschuss, (float) $applicationRequestData['beantragterZuschuss']);
      static::assertSame($newBeantragterZuschuss, $applicationProcessData['amount_requested']);
      static::assertSame(0.0, $applicationProcessData['amount_eligible']);

      // Amount of cost item for Sachkostenpauschale should be changed.
      static::assertEquals(
        ['amount' => $newSachkostenpauschale] + $this->sachkostenpauschaleCostItem->toArray(),
        FundingApplicationCostItem::get(FALSE)
          ->addWhere('id', '=', $this->sachkostenpauschaleCostItem->getId())
          ->execute()
          ->single()
      );

      // Cost item for Personalkosten should be unchanged.
      static::assertEquals(
        $this->personalkostenCostItem->toArray(),
        FundingApplicationCostItem::get(FALSE)
          ->addWhere('id', '=', $this->personalkostenCostItem->getId())
          ->execute()
          ->single()
      );

      static::assertCount(0, FundingClearingCostItem::get(FALSE)->execute());
    });
  }

  /**
   * @param "not-started"|"draft"|"accepted" $clearingProcessStatus
   * @param "new"|"applied"|"draft"|"eligible" $applicationProcessStatus
   * @param "open"|"ongoing"|"cleared" $fundingCaseStatus
   */
  private function createFixtures(
    float $sachkostenpauschale,
    int $foerderquote,
    float $personalkostenBeantragt,
    string $clearingProcessStatus,
    string $applicationProcessStatus = 'eligible',
    string $fundingCaseStatus = FundingCaseStatus::ONGOING,
  ): void {
    $personalkostenEligible = round($foerderquote * $personalkostenBeantragt / 100, 2);
    $beantragterZuschuss = round($personalkostenEligible + $sachkostenpauschale, 2);

    $recipientContact = ContactFixture::addOrganization();
    $this->clearingProcessBundle = ClearingProcessBundleFixture::create(
      clearingProcessValues: ['status' => $clearingProcessStatus],
      applicationProcessValues: [
        'request_data' => [
          'sachkostenpauschale' => $sachkostenpauschale,
          'foerderquote' => $foerderquote,
          'internerBezeichner' => 'x',
          'name' => 'Bar',
          'vorname' => 'Foo',
          'tarifUndEingruppierung' => 'test',
          'beginn' => '2026-03-18',
          'ende' => '2026-03-19',
          'personalkostenTatsaechlich' => $personalkostenBeantragt + 100,
          'personalkostenBeantragt' => $personalkostenBeantragt,
          'beantragterZuschuss' => $beantragterZuschuss,
          'empfaenger' => $recipientContact['id'],
          'dokumente' => [
            [
              '_identifier' => 'dokument/0fd1443f-400e-41f1-99b8-42a4034cd048',
              'datei' => 'http://example.org/test.txt',
              'beschreibung' => 'test',
            ],
          ],
          'titel' => 'Personalkostenförderung Foo Bar',
          'kurzbeschreibung' => 'Personalkostenförderung Foo Bar',
        ],
        'amount_requested' => $beantragterZuschuss,
        'amount_eligible' => 'eligible' === $applicationProcessStatus ? $beantragterZuschuss : 0.0,
        'status' => $applicationProcessStatus,
        'is_eligible' => 'eligible' === $applicationProcessStatus,
        'start_date' => '2026-03-18 00:00:00',
        'end_date' => '2026-03-19 00:00:00',
      ],
      fundingCaseValues: [
        'status' => $fundingCaseStatus,
        'recipient_contact_id' => $recipientContact['id'],
        'amount_approved' => 'open' === $fundingCaseStatus ? 0.0 : $beantragterZuschuss,
      ],
      fundingCaseTypeValues: ['name' => PersonalkostenMetaData::NAME],
      fundingProgramValues: [
        'start_date' => '2026-03-18 00:00:00',
        'end_date' => '2026-03-19 00:00:00',
        'funding_program_extra.foerderquote' => $foerderquote,
        'funding_program_extra.sachkostenpauschale' => $sachkostenpauschale,
      ]
    );
    $applicationProcess = $this->clearingProcessBundle->getApplicationProcess();
    $clearingProcess = $this->clearingProcessBundle->getClearingProcess();

    $this->sachkostenpauschaleCostItem = ApplicationCostItemFixture::addFixture($applicationProcess->getId(), [
      'type' => 'sachkostenpauschale',
      'identifier' => 'sachkostenpauschale',
      'amount' => $sachkostenpauschale,
    ]);
    $this->personalkostenCostItem = ApplicationCostItemFixture::addFixture($applicationProcess->getId(), [
      'type' => 'personalkosten',
      'identifier' => 'personalkosten',
      'amount' => $personalkostenBeantragt,
      'data_pointer' => '/personalkostenBeantragt',
    ]);

    if ('not-started' !== $clearingProcessStatus) {
      $clearingCostItemStatus = 'accepted' === $clearingProcessStatus ? 'accepted' : 'new';
      $this->sachkostenpauschaleClearingCostItem = ClearingCostItemFixture::addFixture(
        $clearingProcess->getId(),
        $this->sachkostenpauschaleCostItem->getId(),
        [
          'status' => $clearingCostItemStatus,
          'amount' => $sachkostenpauschale,
          'amount_admitted' => 'accepted' === $clearingProcessStatus ? $sachkostenpauschale : NULL,
        ]
      );
      $this->personalkostenClearingCostItem = ClearingCostItemFixture::addFixture(
        $clearingProcess->getId(),
        $this->personalkostenCostItem->getId(),
        [
          'status' => $clearingCostItemStatus,
          'amount' => $personalkostenBeantragt,
          'amount_admitted' => 'accepted' === $clearingProcessStatus ? $personalkostenEligible : NULL,
        ]
      );
    }
  }

  /**
   * @param callable(): void $test
   */
  private function doTest(callable $test): void {
    // Creating custom groups and custom fields changes the DB schema and thus flushes the transaction.
    \Civi\Core\Transaction\Manager::singleton()->forceRollback();

    $customGroup = CustomGroup::create(FALSE)
      ->setValues([
        'name' => 'funding_program_extra',
        'title' => 'FundingProgram Extra',
        'extends' => 'FundingProgram',
      ])->execute()->single();

    try {
      $customFieldFoerderquote = CustomField::create(FALSE)->setValues([
        'custom_group_id' => $customGroup['id'],
        'name' => 'foerderquote',
        'label' => 'Förderquote',
        'data_type' => 'Int',
        'html_type' => 'Text',
      ])->execute()->single();

      $customFieldSachkostenpauschale = CustomField::create(FALSE)->setValues([
        'custom_group_id' => $customGroup['id'],
        'name' => 'sachkostenpauschale',
        'label' => 'Sachkostenpauschale',
        'data_type' => 'Money',
        'html_type' => 'Text',
      ])->execute()->single();

      \Civi\Core\Transaction\Manager::singleton()->inc();
      \Civi\Core\Transaction\Manager::singleton()->getFrame()->setRollbackOnly();

      $test();
    }
    finally {
      \Civi\Core\Transaction\Manager::singleton()->forceRollback();

      if (isset($customFieldSachkostenpauschale)) {
        CustomField::delete(FALSE)->addWhere('id', '=', $customFieldSachkostenpauschale['id'])->execute();
      }
      if (isset($customFieldFoerderquote)) {
        CustomField::delete(FALSE)->addWhere('id', '=', $customFieldFoerderquote['id'])->execute();
      }

      CustomGroup::delete(FALSE)->addWhere('id', '=', $customGroup['id'])->execute();

      // There needs to be an open transaction to prevent an error when the CiviCRM test listener tries to rollback a
      // transaction.
      \Civi\Core\Transaction\Manager::singleton()->inc();
      \Civi\Core\Transaction\Manager::singleton()->getFrame()->setRollbackOnly();
    }
  }

}
