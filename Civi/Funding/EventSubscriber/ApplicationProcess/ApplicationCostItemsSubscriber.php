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

use Civi\Funding\ApplicationProcess\Command\ApplicationCostItemsPersistCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationFormSubmitSuccessEvent;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationCostItemsSubscriber implements EventSubscriberInterface {

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationFormSubmitSuccessEvent::class => 'onFormSubmitSuccess',
    ];
  }

  public function __construct(
    private readonly ApplicationCostItemsPersistHandlerInterface $costItemsPersistHandler,
    private readonly FundingCaseTypeMetaDataProviderInterface $metaDataProvider,
  ) {}

  public function onFormSubmitSuccess(ApplicationFormSubmitSuccessEvent $event): void {
    if (
      $event->getResult()->getValidationResult()->isReadOnly()
      || NULL !== $event->getApplicationProcess()->getRestoredSnapshot()
      || $this->isDeleteAction($event->getAction(), $event->getFundingCaseType())
    ) {
      return;
    }

    $this->costItemsPersistHandler->handle(new ApplicationCostItemsPersistCommand(
      $event->getApplicationProcessBundle(),
      $event->getValidatedData()->getCostItemsData()
    ));
  }

  private function isDeleteAction(string $action, FundingCaseTypeEntity $fundingCaseType): bool {
    $metaData = $this->metaDataProvider->get($fundingCaseType->getName());

    return (bool) $metaData->getApplicationProcessAction($action)?->isDelete();
  }

}
