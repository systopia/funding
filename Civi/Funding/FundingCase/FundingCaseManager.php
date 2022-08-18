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

namespace Civi\Funding\FundingCase;

use Civi\Api4\FundingCase;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @phpstan-type fundingCaseT array{
 *   id: int,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   recipient_contact_id: int,
 * }
 */
class FundingCaseManager {

  private Api4Interface $api4;

  private CiviEventDispatcher $eventDispatcher;

  public function __construct(Api4Interface $api4, CiviEventDispatcher $eventDispatcher) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @phpstan-param array{
   *   funding_program: array<string, mixed>&array{id: int, permissions: array<string>},
   *   funding_case_type: array<string, mixed>&array{id: int},
   *   recipient_contact_id: int,
   * } $values
   *
   * @throws \API_Exception
   */
  public function create(int $contactId, array $values): FundingCaseEntity {
    $now = date('Y-m-d H:i:s');
    $fundingCase = FundingCaseEntity::fromArray([
      'funding_program_id' => $values['funding_program']['id'],
      'funding_case_type_id' => $values['funding_case_type']['id'],
      'recipient_contact_id' => $values['recipient_contact_id'],
      'status' => 'open',
      'creation_date' => $now,
      'modification_date' => $now,
    ]);
    $action = FundingCase::create()->setValues($fundingCase->toArray());

    /** @phpstan-var fundingCaseT $fundingCaseValues */
    $fundingCaseValues = $this->api4->executeAction($action)->first();
    $fundingCase = FundingCaseEntity::fromArray($fundingCaseValues);

    $event = new FundingCaseCreatedEvent($contactId, $fundingCase,
      $values['funding_program'], $values['funding_case_type']);
    $this->eventDispatcher->dispatch(FundingCaseCreatedEvent::class, $event);

    // Fetch permissions
    $action = FundingCase::get()->setContactId($contactId)
      ->addWhere('id', '=', $fundingCase->getId());
    /** @phpstan-var fundingCaseT $fundingCaseValues */
    $fundingCaseValues = $this->api4->executeAction($action)->first();
    $fundingCase->setValues($fundingCaseValues);

    return $fundingCase;
  }

  public function update(FundingCaseEntity $fundingCase): void {
    $action = FundingCase::update()->setValues($fundingCase->toArray());
    $this->api4->executeAction($action);
  }

  public function hasAccess(int $contactId, int $id): bool {
    $action = FundingCase::get()
      ->setContactId($contactId)
      ->addSelect('id')
      ->addWhere('id', '=', $id);

    return 1 === $this->api4->executeAction($action)->count();
  }

}
