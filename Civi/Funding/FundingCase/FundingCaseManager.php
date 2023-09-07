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
use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\Util\Uuid;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\Api4\Query\ConditionInterface;
use Webmozart\Assert\Assert;

class FundingCaseManager {

  private Api4Interface $api4;

  private FundingAttachmentManagerInterface $attachmentManager;

  private CiviEventDispatcherInterface $eventDispatcher;

  /**
   * @phpstan-var array<int, bool>
   */
  private array $accessAllowed = [];

  public function __construct(
    Api4Interface $api4,
    FundingAttachmentManagerInterface $attachmentManager,
    CiviEventDispatcherInterface $eventDispatcher
  ) {
    $this->api4 = $api4;
    $this->attachmentManager = $attachmentManager;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * @phpstan-return array<FundingCaseEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getBy(ConditionInterface $condition): array {
    $action = FundingCase::get(FALSE)
      ->setWhere([$condition->toArray()]);

    return FundingCaseEntity::allFromApiResult($this->api4->executeAction($action));
  }

  public function clearCache(): void {
    $this->accessAllowed = [];
  }

  /**
   * @phpstan-param array{
   *   funding_program: \Civi\Funding\Entity\FundingProgramEntity,
   *   funding_case_type: \Civi\Funding\Entity\FundingCaseTypeEntity,
   *   recipient_contact_id: int,
   * } $values
   *
   * @throws \CRM_Core_Exception
   */
  public function create(int $contactId, array $values): FundingCaseEntity {
    $now = date('Y-m-d H:i:s');
    $fundingCase = FundingCaseEntity::fromArray([
      // Initialize with random UUID
      'identifier' => Uuid::generateRandom(),
      'funding_program_id' => $values['funding_program']->getId(),
      'funding_case_type_id' => $values['funding_case_type']->getId(),
      'recipient_contact_id' => $values['recipient_contact_id'],
      'status' => 'open',
      'creation_date' => $now,
      'modification_date' => $now,
      'creation_contact_id' => $contactId,
      'amount_approved' => NULL,
    ]);
    $action = FundingCase::create(FALSE)
      ->setValues($fundingCase->toArray());

    $fundingCase = FundingCaseEntity::singleFromApiResult($this->api4->executeAction($action))->reformatDates();

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
    $action = FundingCase::delete(FALSE)
      ->addWhere('id', '=', $fundingCase->getId());

    $this->api4->executeAction($action);

    $event = new FundingCaseDeletedEvent($fundingCase);
    $this->eventDispatcher->dispatch(FundingCaseDeletedEvent::class, $event);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $id): ?FundingCaseEntity {
    $action = FundingCase::get(FALSE)
      ->addWhere('id', '=', $id);

    return FundingCaseEntity::singleOrNullFromApiResult($this->api4->executeAction($action));
  }

  /**
   * @phpstan-return array<FundingCaseEntity>
   *
   * @throws \CRM_Core_Exception
   */
  public function getAll(): array {
    $action = FundingCase::get(FALSE);

    return FundingCaseEntity::allFromApiResult($this->api4->executeAction($action));
  }

  /**
   * Returns a funding case in status open with the given funding program,
   * funding case type, and recipient contact. If no such funding case exists,
   * a new one will be created.
   *
   * @phpstan-param array{
   *   funding_program: \Civi\Funding\Entity\FundingProgramEntity,
   *   funding_case_type: \Civi\Funding\Entity\FundingCaseTypeEntity,
   *   recipient_contact_id: int,
   *   title?: string|null,
   * } $values
   *
   * @throws \CRM_Core_Exception
   */
  public function getOpenOrCreate(int $contactId, array $values): FundingCaseEntity {
    $fundingCase = $this->getLastBy(CompositeCondition::fromFieldValuePairs([
      'funding_program_id' => $values['funding_program']->getId(),
      'recipient_contact_id' => $values['recipient_contact_id'],
      'status' => 'open',
    ]));

    return $fundingCase ?? $this->create($contactId, $values);
  }

  public function update(FundingCaseEntity $fundingCase): void {
    $previousFundingCase = $this->get($fundingCase->getId());
    Assert::notNull($previousFundingCase, 'Funding case could not be loaded');
    if ($fundingCase->getModificationDate() == $previousFundingCase->getModificationDate()) {
      $fundingCase->setModificationDate(new \DateTime(date('Y-m-d H:i:s')));
    }
    $action = FundingCase::update(FALSE)
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
   * @throws \CRM_Core_Exception
   */
  public function hasAccess(int $id): bool {
    if (!isset($this->accessAllowed[$id])) {
      $action = FundingCase::get(FALSE)
        ->addSelect('id')
        ->addWhere('id', '=', $id);

      $this->accessAllowed[$id] = 1 === $this->api4->executeAction($action)->count();
    }

    return $this->accessAllowed[$id];
  }

  /**
   * @return bool
   *   TRUE if the funding case with the given ID has a transfer contract.
   *
   * @throws \CRM_Core_Exception
   */
  public function hasTransferContract(int $id): bool {
    return $this->attachmentManager->has('civicrm_funding_case', $id, FileTypeNames::TRANSFER_CONTRACT);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getLastBy(ConditionInterface $condition): ?FundingCaseEntity {
    $result = $this->api4->getEntities(
      FundingCase::_getEntityName(),
      $condition,
      ['id' => 'DESC'],
      1,
      0,
      ['checkPermissions' => FALSE],
    );

    return FundingCaseEntity::singleOrNullFromApiResult($result);
  }

}
