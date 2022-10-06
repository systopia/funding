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
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationResourcesItemsFactory;
use Civi\Funding\Util\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @phpstan-type finanzierungT array{
 *   teilnehmerbeitraege: float,
 *   eigenmittel: float,
 *   oeffentlicheMittel: array{
 *     europa: float,
 *     bundeslaender: float,
 *     staedteUndKreise: float,
 *   },
 *   sonstigeMittel: array<array{
 *     _identifier?: string,
 *     quelle: string,
 *     betrag: float,
 *   }>,
 * }
 */
class AVK1ApplicationResourcesItemSubscriber implements EventSubscriberInterface {

  private ApplicationResourcesItemManager $applicationResourcesItemManager;

  private AVK1ApplicationResourcesItemsFactory $applicationResourcesItemsFactory;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  public function __construct(
    ApplicationResourcesItemManager $applicationResourcesItemManager,
    AVK1ApplicationResourcesItemsFactory $applicationResourcesItemsFactory,
    FundingCaseTypeManager $fundingCaseTypeManager
  ) {
    $this->applicationResourcesItemManager = $applicationResourcesItemManager;
    $this->applicationResourcesItemsFactory = $applicationResourcesItemsFactory;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreCreateEvent::class => 'onPreCreate',
      ApplicationProcessCreatedEvent::class => 'onCreated',
      ApplicationProcessPreUpdateEvent::class => 'onPreUpdate',
      ApplicationProcessUpdatedEvent::class => 'onUpdated',
    ];
  }

  public function onPreCreate(ApplicationProcessPreCreateEvent $event): void {
    if ($this->isSupportedFundingCase($event->getFundingCase())) {
      $this->addResourcesItemIdentifiers($event->getApplicationProcess());
    }
  }

  /**
   * @throws \API_Exception
   */
  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    if ($this->isSupportedFundingCase($event->getFundingCase())) {
      $this->updateResourcesItems($event->getApplicationProcess());
    }
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    if ($this->isSupportedFundingCase($event->getFundingCase())) {
      $this->addResourcesItemIdentifiers($event->getApplicationProcess());
    }
  }

  /**
   * @throws \API_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    if ($this->isSupportedFundingCase($event->getFundingCase())
      && $event->getApplicationProcess()->getRequestData()['finanzierung']
      !== $event->getPreviousApplicationProcess()->getRequestData()['finanzierung']) {
      $this->updateResourcesItems($event->getApplicationProcess());
    }
  }

  private function addResourcesItemIdentifiers(ApplicationProcessEntity $applicationProcess): void {
    $requestData = $applicationProcess->getRequestData();
    /** @phpstan-var finanzierungT $finanzierung */
    $finanzierung = &$requestData['finanzierung'];
    $finanzierung['sonstigeMittel'] = $this->addIdentifiers($finanzierung['sonstigeMittel']);

    $applicationProcess->setRequestData($requestData);
  }

  /**
   * @param array<array<string, mixed>> $array
   *
   * @return array<array<string, mixed>>
   */
  private function addIdentifiers(array $array): array {
    foreach ($array as &$item) {
      if ('' === ($item['_identifier'] ?? '')) {
        $item['_identifier'] = Uuid::generateRandom();
      }
    }

    return $array;
  }

  /**
   * @throws \API_Exception
   */
  private function updateResourcesItems(ApplicationProcessEntity $applicationProcess): void {
    $items = $this->applicationResourcesItemsFactory->createItems($applicationProcess);
    $this->applicationResourcesItemManager->updateAll($applicationProcess->getId(), $items);
  }

  private function isSupportedFundingCase(FundingCaseEntity $fundingCase): bool {
    return $this->fundingCaseTypeManager->getIdByName('AVK1SonstigeAktivitaet')
      === $fundingCase->getFundingCaseTypeId();
  }

}
