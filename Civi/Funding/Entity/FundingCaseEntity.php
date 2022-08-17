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

/**
 * @phpstan-type fundingCaseT array{
 *   id?: int,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   recipient_contact_id: int,
 *   creation_date: string,
 *   modification_date: string,
 * }
 *
 * @phpstan-method fundingCaseT toArray()
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

  /**
   * @return string Creation date in the form "YmdHis".
   */
  public function getCreationDate(): string {
    return $this->values['creation_date'];
  }

  /**
   * @return string Modification date in the form 'YmdHis'.
   */
  public function getModificationDate(): string {
    return $this->values['modification_date'];
  }

  /**
   * @param string $modificationDate Modification date in the form 'YmdHis'.
   */
  public function setModificationDate(string $modificationDate): self {
    $this->values['modification_date'] = $modificationDate;

    return $this;
  }

  public function getRecipientContactId(): int {
    return $this->values['recipient_contact_id'];
  }

  public function setRecipientContactId(int $recipientContactId): self {
    $this->values['recipient_contact_id'] = $recipientContactId;

    return $this;
  }

}
