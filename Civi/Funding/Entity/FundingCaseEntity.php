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

use Civi\RemoteTools\Api4\RemoteApiConstants;

/**
 * @phpstan-type fundingCaseT array{
 *   id?: int,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   recipient_contact_id: int,
 *   creation_date: string,
 *   modification_date: string,
 *   permissions?: array<string>,
 * }
 *
 * @phpstan-method fundingCaseT toArray()
 * @phpstan-method void setValues(fundingCaseT $values)
 */
final class FundingCaseEntity extends AbstractEntity {

  /**
   * @var array
   * @phpstan-var fundingCaseT
   */
  protected array $values;

  /**
   * @phpstan-param fundingCaseT $values
   */
  public static function fromArray(array $values): self {
    return new self($values);
  }

  /**
   * @phpstan-param fundingCaseT $values
   */
  public function __construct(array $values) {
    parent::__construct($values);
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

  /**
   * @phpstan-return array<string, bool>
   *   Permissions with key as permission prefixed by
   *   RemoteApiConstants::PERMISSIONS_FIELD_PREFIX.
   */
  public function getFlattenedPermissions(): array {
    /** @phpstan-var array<string, bool> */
    return array_filter(
      $this->values,
      fn (string $key) => str_starts_with($key, RemoteApiConstants::PERMISSION_FIELD_PREFIX),
      ARRAY_FILTER_USE_KEY,
    );
  }

  /**
   * @phpstan-return array<string>
   */
  public function getPermissions(): array {
    return $this->values['permissions'] ?? [];
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
