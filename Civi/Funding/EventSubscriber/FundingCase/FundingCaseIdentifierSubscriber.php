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

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Api4\FundingCase;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\FundingCase\FundingCaseIdentifierGeneratorInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FundingCaseIdentifierSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  private FundingCaseIdentifierGeneratorInterface $identifierGenerator;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [FundingCaseCreatedEvent::class => ['onCreated', PHP_INT_MAX]];
  }

  public function __construct(
    Api4Interface $api4,
    FundingCaseIdentifierGeneratorInterface $identifierGenerator
  ) {
    $this->api4 = $api4;
    $this->identifierGenerator = $identifierGenerator;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(FundingCaseCreatedEvent $event): void {
    $fundingCase = $event->getFundingCase();
    $identifier = $this->identifierGenerator->generateIdentifier(
      $fundingCase,
      $event->getFundingCaseType(),
      $event->getFundingProgram(),
    );

    $fundingCase->setIdentifier($identifier);
    $this->api4->updateEntity(
      FundingCase::_getEntityName(),
      $fundingCase->getId(),
      ['identifier' => $identifier],
      ['checkPermissions' => FALSE],
    );
  }

}
