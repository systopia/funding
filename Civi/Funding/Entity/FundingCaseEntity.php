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

namespace Civi\Funding\Entity;

use Civi\Funding\Api4\RemoteApiConstants;

/**
 * @phpstan-type fundingCaseT array{
 *   id?: int,
 *   identifier: string,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   recipient_contact_id: int,
 *   creation_date: string,
 *   modification_date: string,
 *   creation_contact_id: int,
 *   notification_contact_ids: list<int>,
 *   amount_approved: ?float,
 *   permissions?: list<string>,
 *   transfer_contract_uri?: ?string,
 * }
 *
 * @phpstan-extends AbstractEntity<fundingCaseT>
 */
final class FundingCaseEntity extends AbstractEntity {

  public function getIdentifier(): string {
    return $this->values['identifier'];
  }

  public function setIdentifier(string $identifier): self {
    $this->values['identifier'] = $identifier;

    return $this;
  }

  public function getFundingProgramId(): int {
    return $this->values['funding_program_id'];
  }

  public function getFundingCaseTypeId(): int {
    return $this->values['funding_case_type_id'];
  }

  public function getStatus(): string {
    return $this->values['status'];
  }

  /**
   * @param string $status
   *
   * @return $this
   */
  public function setStatus(string $status): self {
    $this->values['status'] = $status;

    return $this;
  }

  /**
   * @phpstan-param list<string> $statusList
   *
   * @return bool
   *   TRUE, if the funding case is in one of the given status, FALSE otherwise.
   */
  public function isStatusIn(array $statusList): bool {
    return in_array($this->values['status'], $statusList, TRUE);
  }

  public function getCreationDate(): \DateTime {
    return new \DateTime($this->values['creation_date']);
  }

  public function getModificationDate(): \DateTime {
    return new \DateTime($this->values['modification_date']);
  }

  public function setModificationDate(\DateTimeInterface $modificationDate): self {
    $this->values['modification_date'] = static::toDateTimeStr($modificationDate);

    return $this;
  }

  public function getRecipientContactId(): int {
    return $this->values['recipient_contact_id'];
  }

  public function setRecipientContactId(int $recipientContactId): self {
    $this->values['recipient_contact_id'] = $recipientContactId;

    return $this;
  }

  public function getCreationContactId(): int {
    return $this->values['creation_contact_id'];
  }

  public function setCreationContactId(int $creationContactId): self {
    $this->values['creation_contact_id'] = $creationContactId;

    return $this;
  }

  /**
   * @phpstan-return list<int>
   */
  public function getNotificationContactIds(): array {
    return $this->values['notification_contact_ids'];
  }

  /**
   * @phpstan-param list<int> $notificationContactIds
   */
  public function setNotificationContactIds(array $notificationContactIds): self {
    $this->values['notification_contact_ids'] = $notificationContactIds;

    return $this;
  }

  public function getAmountApproved(): ?float {
    return $this->values['amount_approved'];
  }

  public function setAmountApproved(?float $amountApproved): self {
    $this->values['amount_approved'] = $amountApproved;

    return $this;
  }

  /**
   * @phpstan-return array<string, bool>
   *   Permissions with key as permission prefixed by
   *   RemoteApiConstants::PERMISSIONS_FIELD_PREFIX.
   */
  public function getFlattenedPermissions(): array {
    // @phpstan-ignore return.type
    return array_filter(
      $this->values,
      fn (string $key) => str_starts_with($key, RemoteApiConstants::PERMISSION_FIELD_PREFIX),
      ARRAY_FILTER_USE_KEY,
    );
  }

  /**
   * @phpstan-return list<string>
   */
  public function getPermissions(): array {
    return $this->values['permissions'] ?? [];
  }

  public function hasPermission(string $permission): bool {
    return in_array($permission, $this->getPermissions(), TRUE);
  }

  public function getTransferContractUri(): ?string {
    return $this->values['transfer_contract_uri'] ?? NULL;
  }

  public function setTransferContractUri(?string $transferContractUri): self {
    $this->values['transfer_contract_uri'] = $transferContractUri;

    return $this;
  }

  /**
   * On create CiviCRM returns a different date format than on get. This method
   * reformats the dates in $values so that they are as on get.
   *
   * @internal
   */
  public function reformatDates(): self {
    $this->values['creation_date'] = static::toDateTimeStr($this->getCreationDate());
    $this->setModificationDate($this->getModificationDate());

    return $this;
  }

}
