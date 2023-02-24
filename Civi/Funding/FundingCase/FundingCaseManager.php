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
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Event\FundingCase\FundingCaseDeletedEvent;
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type fundingCaseT array{
 *   id: int,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   recipient_contact_id: int,
 *   creation_contact_id: int,
 * }
 */
class FundingCaseManager {

  private Api4Interface $api4;

  private CiviEventDispatcherInterface $eventDispatcher;

  public function __construct(Api4Interface $api4, CiviEventDispatcherInterface $eventDispatcher) {
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @phpstan-param array{
   *   funding_program: \Civi\Funding\Entity\FundingProgramEntity,
   *   funding_case_type: \Civi\Funding\Entity\FundingCaseTypeEntity,
   *   recipient_contact_id: int,
   * } $values
   *
   * @throws \API_Exception
   */
  public function create(int $contactId, array $values): FundingCaseEntity {
    $now = date('Y-m-d H:i:s');
    $fundingCase = FundingCaseEntity::fromArray([
      'funding_program_id' => $values['funding_program']->getId(),
      'funding_case_type_id' => $values['funding_case_type']->getId(),
      'recipient_contact_id' => $values['recipient_contact_id'],
      'status' => 'open',
      'creation_date' => $now,
      'modification_date' => $now,
      'creation_contact_id' => $contactId,
    ]);
    $action = FundingCase::create()
      ->setCheckPermissions(FALSE)
      ->setValues($fundingCase->toArray());

    /** @phpstan-var fundingCaseT $fundingCaseValues */
    $fundingCaseValues = $this->api4->executeAction($action)->first();
    $fundingCase = FundingCaseEntity::fromArray($fundingCaseValues)->reformatDates();

    $event = new FundingCaseCreatedEvent($contactId, $fundingCase,
      $values['funding_program'], $values['funding_case_type']);
    $this->eventDispatcher->dispatch(FundingCaseCreatedEvent::class, $event);

    // Fetch permissions
    $persistedFundingCase = $this->get($fundingCase->getId());
    Assert::notNull($persistedFundingCase, 'Funding case could not be loaded');
    $fundingCase->setValues($persistedFundingCase->toArray());

    return $fundingCase;
  }

  public function delete(FundingCaseEntity $fundingCase): void {
    $action = FundingCase::delete()
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $fundingCase->getId());

    $this->api4->executeAction($action);

    $event = new FundingCaseDeletedEvent($fundingCase);
    $this->eventDispatcher->dispatch(FundingCaseDeletedEvent::class, $event);
  }

  public function get(int $id): ?FundingCaseEntity {
    $action = FundingCase::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $id);
    /** @phpstan-var fundingCaseT|null $values */
    $values = $this->api4->executeAction($action)->first();

    if (NULL === $values) {
      return NULL;
    }

    return FundingCaseEntity::fromArray($values);
  }

  /**
   * @phpstan-return array<FundingCaseEntity>
   *
   * @throws \API_Exception
   */
  public function getAll(): array {
    $action = FundingCase::get()
      ->setCheckPermissions(FALSE);

    /** @var array<fundingCaseT> $records */
    $records = $this->api4->executeAction($action)->getArrayCopy();

    return array_map(
      fn (array $values) => FundingCaseEntity::fromArray($values),
      $records,
    );
  }

  public function update(FundingCaseEntity $fundingCase): void {
    $previousFundingCase = $this->get($fundingCase->getId());
    Assert::notNull($previousFundingCase, 'Funding case could not be loaded');
    $action = FundingCase::update()
      ->setCheckPermissions(FALSE)
      ->setValues($fundingCase->toArray());
    $this->api4->executeAction($action);

    $event = new FundingCaseUpdatedEvent($previousFundingCase, $fundingCase);
    $this->eventDispatcher->dispatch(FundingCaseUpdatedEvent::class, $event);
  }

  /**
   * @return bool
   *   TRUE if the current contact (either remote or local) has access to the
   *   FundingCase with the given ID.
   *
   * @throws \API_Exception
   */
  public function hasAccess(int $id): bool {
    $action = FundingCase::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('id')
      ->addWhere('id', '=', $id);

    return 1 === $this->api4->executeAction($action)->count();
  }

}
