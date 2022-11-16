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

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\DAOUpdateAction;
use Civi\Funding\ApplicationProcess\ApplicationIdentifierGeneratorInterface;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ApplicationProcessIdentifierSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  private ApplicationIdentifierGeneratorInterface $applicationIdentifierGenerator;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [ApplicationProcessCreatedEvent::class => ['onCreated', PHP_INT_MAX]];
  }

  public function __construct(
    Api4Interface $api4,
    ApplicationIdentifierGeneratorInterface $applicationIdentifierGenerator
  ) {
    $this->api4 = $api4;
    $this->applicationIdentifierGenerator = $applicationIdentifierGenerator;
  }

  /**
   * @throws \API_Exception
   */
  public function onCreated(ApplicationProcessCreatedEvent $event): void {
    $applicationProcess = $event->getApplicationProcess();
    $identifier = $this->applicationIdentifierGenerator->generateIdentifier(
      $applicationProcess,
      $event->getFundingCase(),
      $event->getFundingCaseType(),
      $event->getFundingProgram()
    );
    $applicationProcess->setIdentifier($identifier);
    $action = (new DAOUpdateAction(FundingApplicationProcess::_getEntityName(), 'update'))
      ->addValue('identifier', $identifier)
      ->addWhere('id', '=', $applicationProcess->getId());
    $this->api4->executeAction($action);
  }

}
