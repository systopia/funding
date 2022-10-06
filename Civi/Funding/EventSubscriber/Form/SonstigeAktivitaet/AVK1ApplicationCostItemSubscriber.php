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
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\SonstigeAktivitaet\AVK1ApplicationCostItemsFactory;
use Civi\Funding\Util\Uuid;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @phpstan-type kostenT array{
 *   unterkunftUndVerpflegung: float,
 *   honorare: array<array{
 *     _identifier?: string,
 *     stunden: float,
 *     verguetung: float,
 *     zweck: string,
 *   }>,
 *   fahrtkosten: array{
 *     intern: float,
 *     anTeilnehmerErstattet: float,
 *   },
 *   sachkosten: array{
 *     haftungKfz: float,
 *     ausstattung: array<array{
 *       _identifier?: string,
 *       gegenstand: string,
 *       betrag: float,
 *     }>,
 *   },
 *   sonstigeAusgaben: array<array{
 *     _identifier?: string,
 *     betrag: float,
 *     zweck: string,
 *   }>,
 *   versicherungTeilnehmer: float,
 * }
 */
class AVK1ApplicationCostItemSubscriber implements EventSubscriberInterface {

  private ApplicationCostItemManager $applicationCostItemManager;

  private AVK1ApplicationCostItemsFactory $applicationCostItemsFactory;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  public function __construct(
    ApplicationCostItemManager $applicationCostItemManager,
    AVK1ApplicationCostItemsFactory $applicationCostItemsFactory,
    FundingCaseTypeManager $fundingCaseTypeManager
  ) {
    $this->applicationCostItemManager = $applicationCostItemManager;
    $this->applicationCostItemsFactory = $applicationCostItemsFactory;
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
      $this->addCostItemIdentifiers($event->getApplicationProcess());
    }
  }

  /**
   * @throws \API_Exception
   */
  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    if ($this->isSupportedFundingCase($event->getFundingCase())) {
      $this->updateCostItems($event->getApplicationProcess());
    }
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    if ($this->isSupportedFundingCase($event->getFundingCase())) {
      $this->addCostItemIdentifiers($event->getApplicationProcess());
    }
  }

  /**
   * @throws \API_Exception
   */
  public function onUpdated(ApplicationProcessUpdatedEvent $event): void {
    if ($this->isSupportedFundingCase($event->getFundingCase())
      && $event->getApplicationProcess()->getRequestData()['kosten']
      !== $event->getPreviousApplicationProcess()->getRequestData()['kosten']) {
      $this->updateCostItems($event->getApplicationProcess());
    }
  }

  private function addCostItemIdentifiers(ApplicationProcessEntity $applicationProcess): void {
    $requestData = $applicationProcess->getRequestData();
    /** @phpstan-var kostenT $kosten */
    $kosten = &$requestData['kosten'];
    $kosten['honorare'] = $this->addIdentifiers($kosten['honorare']);
    $kosten['sachkosten']['ausstattung'] = $this->addIdentifiers($kosten['sachkosten']['ausstattung']);
    $kosten['sonstigeAusgaben'] = $this->addIdentifiers($kosten['sonstigeAusgaben']);

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
  private function updateCostItems(ApplicationProcessEntity $applicationProcess): void {
    $items = $this->applicationCostItemsFactory->createItems($applicationProcess);
    $this->applicationCostItemManager->updateAll($applicationProcess->getId(), $items);
  }

  private function isSupportedFundingCase(FundingCaseEntity $fundingCase): bool {
    return $this->fundingCaseTypeManager->getIdByName('AVK1SonstigeAktivitaet')
      === $fundingCase->getFundingCaseTypeId();
  }

}
