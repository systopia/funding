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

use Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationResourcesItemsFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet\AVK1ApplicationResourcesItemSubscriber
 */
final class AVK1ApplicationResourcesItemSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationResourcesItemManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationResourcesItemManagerMock;

  /**
   * @var \Civi\Funding\SonstigeAktivitaet\AVK1ApplicationResourcesItemsFactory&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $applicationResourcesItemsFactoryMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseTypeManagerMock;

  private AVK1ApplicationResourcesItemSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationResourcesItemManagerMock = $this->createMock(ApplicationResourcesItemManager::class);
    $this->applicationResourcesItemsFactoryMock = $this->createMock(AVK1ApplicationResourcesItemsFactory::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->subscriber = new AVK1ApplicationResourcesItemSubscriber(
      $this->applicationResourcesItemManagerMock,
      $this->applicationResourcesItemsFactoryMock,
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

    static::assertEquals($expectedSubscriptions, AVK1ApplicationResourcesItemSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists(AVK1ApplicationResourcesItemSubscriber::class, $method));
    }
  }

  public function testOnPreCreate(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'finanzierung' => [
        'sonstigeMittel' => [
          ['_identifier' => '', 'quelle' => 'Test', 'betrag' => 1.23],
        ],
      ],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $event = new ApplicationProcessPreCreateEvent(
      11,
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram
    );
    $this->subscriber->onPreCreate($event);
    /** @phpstan-var array{finanzierung: array{sonstigeMittel: array<array<string, mixed>>}} $requestData */
    $requestData = $applicationProcess->getRequestData();
    static::assertNotEmpty($requestData['finanzierung']['sonstigeMittel'][0]['_identifier']);
  }

  public function testOnPreUpdate(): void {
    $fundingCase = $this->createFundingCase();
    $previousApplicationProcess = $this->createApplicationProcess();
    $previousApplicationProcess->setRequestData([
      'finanzierung' => [
        'sonstigeMittel' => [
          ['_identifier' => 'existing', 'quelle' => 'Test', 'betrag' => 1.23],
        ],
      ],
    ]);
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'finanzierung' => [
        'sonstigeMittel' => [
          ['_identifier' => 'existing', 'quelle' => 'Changed', 'betrag' => 1.23],
          ['quelle' => 'New', 'betrag' => 2.34],
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

    /** @phpstan-var array{finanzierung: array{sonstigeMittel: array<array<string, mixed>>}} $requestData */
    $requestData = $applicationProcess->getRequestData();
    $sonstigeMittel = $requestData['finanzierung']['sonstigeMittel'];
    static::assertCount(2, $sonstigeMittel);
    static::assertSame(['_identifier' => 'existing', 'quelle' => 'Changed', 'betrag' => 1.23], $sonstigeMittel[0]);
    static::assertNotEmpty($sonstigeMittel[1]['_identifier']);
  }

  public function testOnCreated(): void {
    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'finanzierung' => [],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $item = $this->createApplicationResourcesItem();
    $this->applicationResourcesItemsFactoryMock->expects(static::once())->method('createItems')
      ->with($applicationProcess)
      ->willReturn([$item]);

    $this->applicationResourcesItemManagerMock->expects(static::once())->method('updateAll')
      ->with($applicationProcess->getId(), [$item]);

    $event = new ApplicationProcessCreatedEvent(
      11,
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram
    );
    $this->subscriber->onCreated($event);
  }

  public function testOnUpdated(): void {
    $fundingCase = $this->createFundingCase();
    $previousApplicationProcess = $this->createApplicationProcess();
    $previousApplicationProcess->setRequestData([
      'finanzierung' => ['test' => 1],
    ]);
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'finanzierung' => ['test' => 2],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $item = $this->createApplicationResourcesItem();
    $this->applicationResourcesItemsFactoryMock->expects(static::once())->method('createItems')
      ->with($applicationProcess)
      ->willReturn([$item]);

    $this->applicationResourcesItemManagerMock->expects(static::once())->method('updateAll')
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
      'finanzierung' => ['test' => 1],
    ]);
    $applicationProcess = $this->createApplicationProcess();
    $applicationProcess->setRequestData([
      'finanzierung' => ['test' => 1],
    ]);

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('getIdByName')
      ->with('AVK1SonstigeAktivitaet')
      ->willReturn($fundingCase->getFundingCaseTypeId());

    $this->applicationResourcesItemsFactoryMock->expects(static::never())->method('createItems');
    $this->applicationResourcesItemManagerMock->expects(static::never())->method('updateAll');

    $event = new ApplicationProcessUpdatedEvent(
      11,
      $previousApplicationProcess,
      $applicationProcess,
      $fundingCase,
    );
    $this->subscriber->onUpdated($event);
  }

  private function createFundingCase(): FundingCaseEntity {
    return FundingCaseFactory::createFundingCase();
  }

  private function createApplicationProcess(): ApplicationProcessEntity {
    return ApplicationProcessFactory::createApplicationProcess();
  }

  private function createApplicationResourcesItem(): ApplicationResourcesItemEntity {
    return ApplicationResourcesItemEntity::fromArray([
      'application_process_id' => 2,
      'identifier' => 'testIdentifier',
      'type' => 'testType',
      'amount' => 1.23,
      'properties' => ['foo' => 'bar'],
    ]);
  }

}
