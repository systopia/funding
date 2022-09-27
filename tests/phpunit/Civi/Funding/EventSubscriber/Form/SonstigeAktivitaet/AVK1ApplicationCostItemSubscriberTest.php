<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationCostItemsFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1ApplicationCostItemSubscriber
 *
 * @phpstan-type kostenSimplifiedT array{
 *   kosten: array{
 *     honorare: array<array<string, mixed>>,
 *     sachkosten: array{ausstattung: array<array<string, mixed>>},
 *     sonstigeAusgaben: array<array<string, mixed>>,
 *   },
 * }
 */
final class AVK1ApplicationCostItemSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationCostItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationCostItemManagerMock;

  /**
   * @var \Civi\Funding\SonstigeAktivitaet\AVK1ApplicationCostItemsFactory&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationCostItemsFactoryMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseTypeManagerMock;

  private AVK1ApplicationCostItemSubscriber $subscriber;

  private string $now;

  protected function setUp(): void {
    parent::setUp();
    $this->now = date('Y-m-d H:i:s');
    $this->applicationCostItemManagerMock = $this->createMock(ApplicationCostItemManager::class);
    $this->applicationCostItemsFactoryMock = $this->createMock(AVK1ApplicationCostItemsFactory::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->subscriber = new AVK1ApplicationCostItemSubscriber(
      $this->applicationCostItemManagerMock,
      $this->applicationCostItemsFactoryMock,
      $this->fundingCaseTypeManagerMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreCreateEvent::class => 'onPreCreate',
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];

    static::assertEquals($expectedSubscriptions, AVK1ApplicationCostItemSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(AVK1ApplicationCostItemSubscriber::class, $method));
    }
  }

  public function testOnPreCreate(): void {
    $fundingCase = $this->createFundingCase();
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'kosten' => [
        'honorare' => [
          ['_identifier' => '', 'stunden' => 2, 'verguetung' => 3, 'zweck' => 'Test'],
        ],
        'sachkosten' => [
          'ausstattung' => [
            ['gegenstand' => 'Test', 'betrag' => 3],
          ],
        ],
        'sonstigeAusgaben' => [
          ['betrag' => 3, 'zweck' => 'Test'],
        ],
      ],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $event = new ApplicationProcessPreCreateEvent(11, $applicationProcess, $fundingCase);
    $this->subscriber->onPreCreate($event);
    /** @phpstan-var kostenSimplifiedT $requestData */
    $requestData = $applicationProcess->getRequestData();
    static::assertNotEmpty($requestData['kosten']['honorare'][0]['_identifier']);
    static::assertNotEmpty($requestData['kosten']['sachkosten']['ausstattung'][0]['_identifier']);
    static::assertNotEmpty($requestData['kosten']['sonstigeAusgaben'][0]['_identifier']);
  }

  public function testOnPreUpdate(): void {
    $fundingCase = $this->createFundingCase();
    $previousApplicationProcess = $this->createApplicationProcess();
    $previousApplicationProcess->setRequestData([
      'kosten' => [
        'honorare' => [
          ['_identifier' => 'existing1', 'stunden' => 2, 'verguetung' => 3, 'zweck' => 'Test'],
        ],
        'sachkosten' => [
          'ausstattung' => [
            ['_identifier' => 'existing2', 'gegenstand' => 'Test', 'betrag' => 3],
          ],
        ],
        'sonstigeAusgaben' => [
          ['_identifier' => 'existing3', 'betrag' => 3, 'zweck' => 'Test'],
        ],
      ],
    ]);
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'kosten' => [
        'honorare' => [
          ['_identifier' => 'existing1', 'stunden' => 2, 'verguetung' => 3, 'zweck' => 'Test'],
          ['stunden' => 3, 'verguetung' => 4, 'zweck' => 'Test2'],
        ],
        'sachkosten' => [
          'ausstattung' => [
            ['_identifier' => 'existing2', 'gegenstand' => 'Test', 'betrag' => 3],
            ['_identifier' => '', 'gegenstand' => 'Test2', 'betrag' => 4],
          ],
        ],
        'sonstigeAusgaben' => [
          ['_identifier' => 'existing3', 'betrag' => 3, 'zweck' => 'Test'],
          ['_identifier' => '', 'betrag' => 4, 'zweck' => 'Test2'],
        ],
      ],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $event = new ApplicationProcessPreUpdateEvent(
      11,
      $previousApplicationProcess,
      $applicationProcess,
      $fundingCase,
    );
    $this->subscriber->onPreUpdate($event);

    /** @phpstan-var kostenSimplifiedT $requestData */
    $requestData = $applicationProcess->getRequestData();
    $honorare = $requestData['kosten']['honorare'];
    static::assertCount(2, $honorare);
    static::assertSame(
      ['_identifier' => 'existing1', 'stunden' => 2, 'verguetung' => 3, 'zweck' => 'Test'],
      $honorare[0]
    );
    static::assertNotEmpty($honorare[1]['_identifier']);

    $ausstattung = $requestData['kosten']['sachkosten']['ausstattung'];
    static::assertCount(2, $ausstattung);
    static::assertSame(['_identifier' => 'existing2', 'gegenstand' => 'Test', 'betrag' => 3], $ausstattung[0]);
    static::assertNotEmpty($ausstattung[1]['_identifier']);

    $sonstigeAusgaben = $requestData['kosten']['sonstigeAusgaben'];
    static::assertCount(2, $sonstigeAusgaben);
    static::assertSame(['_identifier' => 'existing3', 'betrag' => 3, 'zweck' => 'Test'], $sonstigeAusgaben[0]);
    static::assertNotEmpty($sonstigeAusgaben[1]['_identifier']);
  }

  public function testOnCreated(): void {
    $fundingCase = $this->createFundingCase();
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'kosten' => [],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $item = $this->createApplicationCostItem();
    $this->applicationCostItemsFactoryMock->expects(static::once())->method('createItems')
      ->with($applicationProcess)
      ->willReturn([$item]);

    $this->applicationCostItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), [$item]);

    $event = new ApplicationProcessCreatedEvent(11, $applicationProcess, $fundingCase);
    $this->subscriber->onCreated($event);
  }

  public function testOnUpdated(): void {
    $fundingCase = $this->createFundingCase();
    $previousApplicationProcess = $this->createApplicationProcess();
    $previousApplicationProcess->setRequestData([
      'kosten' => ['test' => 1],
    ]);
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'kosten' => ['test' => 2],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $item = $this->createApplicationCostItem();
    $this->applicationCostItemsFactoryMock->expects(static::once())->method('createItems')
      ->with($applicationProcess)
      ->willReturn([$item]);

    $this->applicationCostItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), [$item]);

    $event = new ApplicationProcessUpdatedEvent(
      11,
      $previousApplicationProcess,
      $applicationProcess,
      $fundingCase,
    );
    $this->subscriber->onUpdated($event);
  }

  public function testOnUpdatedFinanzierungUnchanged(): void {
    $fundingCase = $this->createFundingCase();
    $previousApplicationProcess = $this->createApplicationProcess();
    $previousApplicationProcess->setRequestData([
      'kosten' => ['test' => 1],
    ]);
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'kosten' => ['test' => 1],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $this->applicationCostItemsFactoryMock->expects(static::never())->method('createItems');
    $this->applicationCostItemManagerMock->expects(static::never())->method('updateAll');

    $event = new ApplicationProcessUpdatedEvent(
      11,
      $previousApplicationProcess,
      $applicationProcess,
      $fundingCase,
    );
    $this->subscriber->onUpdated($event);
  }

  private function createFundingCase(): FundingCaseEntity {
    return FundingCaseEntity::fromArray([
      'funding_program_id' => 4,
      'funding_case_type_id' => 5,
      'recipient_contact_id' => 1,
      'status' => 'open',
      'creation_date' => $this->now,
      'modification_date' => $this->now,
      'permissions' => ['test_permission'],
    ]);
  }

  private function createApplicationProcess(): ApplicationProcessEntity {
    return ApplicationProcessEntity::fromArray([
      'id' => 2,
      'funding_case_id' => 3,
      'status' => 'new_status',
      'title' => 'Title',
      'short_description' => 'Description',
      'request_data' => ['foo' => 'bar'],
      'amount_requested' => 1.2,
      'creation_date' => $this->now,
      'modification_date' => $this->now,
      'start_date' => NULL,
      'end_date' => NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'is_review_calculative' => NULL,
    ]);
  }

  private function createApplicationCostItem(): ApplicationCostItemEntity {
    return ApplicationCostItemEntity::fromArray([
      'application_process_id' => 2,
      'identifier' => 'testIdentifier',
      'type' => 'testType',
      'amount' => 1.23,
      'properties' => ['foo' => 'bar'],
    ]);
  }

}
