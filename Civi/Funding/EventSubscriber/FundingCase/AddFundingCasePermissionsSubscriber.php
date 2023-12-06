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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\FundingNewCasePermissions;
use Civi\Funding\Entity\FundingNewCasePermissionsEntity;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Permission\FundingCase\RelationFactory\FundingCaseContactRelationFactory;
use Civi\RemoteTools\Api4\Api4Interface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds permissions to newly created FundingCase.
 *
 * @phpstan-type newCasePermissionsT array{
 *   id: int,
 *   type: string,
 *   properties: array<string, mixed>,
 *   permissions: array<string>,
 * }
 */
class AddFundingCasePermissionsSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  private FundingCaseContactRelationFactory $relationFactory;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [FundingCaseCreatedEvent::class => 'onCreated'];
  }

  public function __construct(Api4Interface $api4, FundingCaseContactRelationFactory $relationFactory) {
    $this->api4 = $api4;
    $this->relationFactory = $relationFactory;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(FundingCaseCreatedEvent $event): void {
    $action = FundingNewCasePermissions::get(FALSE)
      ->addWhere('funding_program_id', '=', $event->getFundingProgram()->getId());

    /** @phpstan-var newCasePermissionsT $newCasePermissions */
    foreach ($this->api4->executeAction($action) as $newCasePermissions) {
      $createAction = FundingCaseContactRelation::create(FALSE)
        ->setValues(
          $this->relationFactory->createFundingCaseContactRelation(
            FundingNewCasePermissionsEntity::fromArray($newCasePermissions),
            $event->getFundingCase(),
          )->toArray()
        );
      $this->api4->executeAction($createAction);
    }
  }

}
