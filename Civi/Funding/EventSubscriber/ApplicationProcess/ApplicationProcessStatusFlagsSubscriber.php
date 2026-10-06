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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

final class ApplicationProcessStatusFlagsSubscriber implements EventSubscriberInterface {

  /**
   * Priority is decreased so other subscribers can change the status before.
   * Subscribers of ApplicationProcessPreCreateEvent and
   * ApplicationProcessPreUpdateEvent depending on the status flags must have a
   * lower priority.
   */
  public const PRIORITY = -100;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreCreateEvent::class => ['onPreCreate', self::PRIORITY],
      ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', self::PRIORITY],
    ];
  }

  public function __construct(
    private readonly FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
    private readonly RequestContextInterface $requestContext
  ) {}

  public function onPreCreate(ApplicationProcessPreCreateEvent $event): void {
    $this->updateStatusFlags($event->getApplicationProcessBundle());
  }

  public function onPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    $this->updateStatusFlags($event->getApplicationProcessBundle());
  }

  private function getMetaData(FundingCaseTypeEntity $fundingCaseType): FundingCaseTypeMetaDataInterface {
    return $this->metaDataProvider->get($fundingCaseType->getName());
  }

  private function updateStatusFlags(ApplicationProcessEntityBundle $applicationProcessBundle): void {
    $metaData = $this->getMetaData($applicationProcessBundle->getFundingCaseType());
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $status = $metaData->getApplicationProcessStatus($applicationProcess->getStatus());
    Assert::notNull($status);

    if (TRUE === $status->isEligible() && TRUE !== $applicationProcess->getIsEligible()) {
      $statementOfEligibilityDate = new \DateTime(\CRM_Utils_Time::date('YmdHis'));
      if (NULL === $applicationProcess->getFirstStatementOfEligibilityDate()) {
        $applicationProcess->setFirstStatementOfEligibilityDate($statementOfEligibilityDate);
        $applicationProcess->setFirstStatementOfEligibilityContactId($this->requestContext->getContactId());
      }

      $applicationProcess->setLastStatementOfEligibilityDate($statementOfEligibilityDate);
      $applicationProcess->setLastStatementOfEligibilityContactId($this->requestContext->getContactId());
    }

    $applicationProcess
      ->setIsEligible($status->isEligible())
      ->setIsInWork($status->isInWork())
      ->setIsRejected($status->isRejected())
      ->setIsWithdrawn($status->isWithdrawn());
  }

}
