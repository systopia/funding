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
 * @phpstan-type activityT array{
 *   id?: int,
 *   source_record_id?: ?int,
 *   activity_type_id?: int,
 *   'activity_type_id:name'?: string,
 *   subject: ?string,
 *   activity_date_time?: ?string,
 *   duration?: ?int,
 *   location?: ?string,
 *   phone_id?: ?int,
 *   details?: ?string,
 *   status_id?: ?int,
 *   priority_id?: ?string,
 *   parent_id?: ?string,
 *   is_test?: bool,
 *   medium_id?: ?int,
 *   is_auto?: bool,
 *   relationship_id?: ?int,
 *   activity_result?: ?string,
 *   is_deleted?: bool,
 *   campaign_id?: ?int,
 *   engagement_level?: ?int,
 *   weight?: ?int,
 *   is_star?: bool,
 *   created_date?: ?string,
 *   modified_date?: ?string,
 *   'status_id:name'?: ?string,
 *   assignee_contact_id?: ?int,
 * }
 *
 * status_id:name can be used on create or update, but is normally not returned
 * on get.
 *
 * assignee_contact_id can be used on create or update, but is not returned on
 * get.
 *
 * @phpstan-extends AbstractEntity<activityT>
 *
 * @codeCoverageIgnore
 */
final class ActivityEntity extends AbstractEntity {

  public function __construct(array $values) {
    if (!isset($values['activity_type_id']) && !isset($values['activity_type_id:name'])) {
      throw new \InvalidArgumentException('Either activity_type_id or activity_type_id:name is required');
    }

    parent::__construct($values);
  }

  public function getSourceRecordId(): ?int {
    return $this->values['source_record_id'] ?? NULL;
  }

  public function setSourceRecordId(?int $sourceRecordId): self {
    $this->values['source_record_id'] = $sourceRecordId;

    return $this;
  }

  /**
   * @return int
   *   -1, if constructed with activity_type_id:name instead of
   *   activity_type_id and not persisted, yet.
   */
  public function getActivityTypeId(): int {
    return $this->values['activity_type_id'] ?? -1;
  }

  public function getSubject(): ?string {
    return $this->values['subject'];
  }

  public function setSubject(?string $subject): self {
    $this->values['subject'] = $subject;

    return $this;
  }

  public function getActivityDateTime(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['activity_date_time'] ?? NULL);
  }

  public function setActivityDateTime(?\DateTimeInterface $activityDateTime): self {
    $this->values['activity_date_time'] = static::toDateTimeStrOrNull($activityDateTime);

    return $this;
  }

  public function getDetails(): ?string {
    return $this->values['details'] ?? NULL;
  }

  public function setDetails(?string $details): self {
    $this->values['details'] = $details;

    return $this;
  }

  public function getStatusId(): ?int {
    return $this->values['status_id'] ?? NULL;
  }

  public function setStatusId(?int $statusId): self {
    $this->values['status_id'] = $statusId;

    return $this;
  }

  public function getCreatedDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['created_date'] ?? NULL);
  }

  public function setCreatedDate(?\DateTimeInterface $createdDate): self {
    $this->values['created_date'] = static::toDateTimeStrOrNull($createdDate);

    return $this;
  }

  public function getModifiedDate(): ?\DateTimeInterface {
    return static::toDateTimeOrNull($this->values['modified_date'] ?? NULL);
  }

  public function setModifiedDate(?\DateTimeInterface $modifiedDate): self {
    $this->values['modified_date'] = static::toDateTimeStrOrNull($modifiedDate);

    return $this;
  }

}
