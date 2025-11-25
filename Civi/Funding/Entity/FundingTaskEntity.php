<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

use Civi\Funding\ActivityStatusNames;
use Civi\Funding\ActivityTypeNames;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type taskNameT from ActivityTypeNames
 *
 * @phpstan-type newFundingTaskT array{
 *    subject: string,
 *    details?: string|null,
 *    'status_id:name'?: string,
 *    assignee_contact_ids?: list<int>,
 *    required_permissions: list<string>|null,
 *    type: string,
 *    affected_identifier: string,
 *    funding_case_id: int,
 *    application_process_id?: int,
 *    clearing_process_id?: int,
 *    payout_process_id?: int,
 *    drawdown_id?: int,
 *    due_date?: \DateTimeInterface|null,
 *    external_url?: string,
 *    external_url_label?: string,
 *  }
 *
 * @phpstan-type fundingTaskT array{
 *   id?: int,
 *   source_record_id: int,
 *   activity_type_id?: int,
 *   'activity_type_id:name': taskNameT,
 *   subject: string,
 *   activity_date_time?: ?string,
 *   duration?: ?int,
 *   location?: ?string,
 *   phone_id?: ?int,
 *   details?: ?string,
 *   status_id?: ?int,
 *   'status_id:name': string,
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
 *   target_contact_id?: list<int>,
 *   assignee_contact_id?: list<int>,
 *   'funding_case_task.required_permissions': list<string>|null,
 *   'funding_case_task.type': string,
 *   'funding_case_task.affected_identifier': string,
 *   'funding_case_task.funding_case_id': int,
 *   'funding_case_task.due_date': string|null,
 *   'funding_case_task.external_url': string|null,
 *   'funding_case_task.external_url_label': string|null,
 *   'funding_application_process_task.application_process_id'?: int|null,
 *   'funding_clearing_process_task.clearing_process_id'?: int|null,
 *   'funding_payout_process_task.payout_process_id'?: int|null,
 *   'funding_drawdown_task.drawdown_id'?: int|null,
 * }
 *
 * status_id:name can be used on create or update, but is normally not returned
 * on get.
 *
 * assignee_contact_id can be used on create or update, but is not returned on
 * get.
 *
 * @phpstan-extends AbstractActivityEntity<fundingTaskT>
 *
 * @codeCoverageIgnore
 *
 * @method int getSourceRecordId()
 */
final class FundingTaskEntity extends AbstractActivityEntity {

  public function __construct(array $values) {
    if (is_string($values['funding_case_task.required_permissions'])) {
      // When loaded from persisted values.
      $values['funding_case_task.required_permissions'] =
        // CiviCRM doesn't persist NULL, but an empty string.
        '' === $values['funding_case_task.required_permissions'] ? NULL
        : json_decode($values['funding_case_task.required_permissions'], TRUE);
    }
    parent::__construct($values);
  }

  /**
   * @phpstan-param newFundingTaskT $values
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public static function newTask(array $values): self {
    // phpcs:enable
    $entityValues = [
      'subject' => $values['subject'],
      'details' => $values['details'] ?? NULL,
      'status_id:name' => $values['status_id:name'] ?? ActivityStatusNames::SCHEDULED,
      'funding_case_task.required_permissions' => $values['required_permissions'],
      'funding_case_task.type' => $values['type'],
      'funding_case_task.affected_identifier' => $values['affected_identifier'],
      'funding_case_task.funding_case_id' => $values['funding_case_id'],
      'funding_case_task.due_date' => self::toDateStrOrNull($values['due_date'] ?? NULL),
      'funding_case_task.external_url' => $values['external_url'] ?? NULL,
      'funding_case_task.external_url_label' => $values['external_url_label'] ?? NULL,
      'funding_application_process_task.application_process_id' => $values['application_process_id'] ?? NULL,
      'funding_clearing_process_task.clearing_process_id' => $values['clearing_process_id'] ?? NULL,
      'funding_payout_process_task.payout_process_id' => $values['payout_process_id'] ?? NULL,
      'funding_drawdown_task.drawdown_id' => $values['drawdown_id'] ?? NULL,
    ];

    if (isset($values['assignee_contact_ids'])) {
      $entityValues['assignee_contact_id'] = $values['assignee_contact_ids'];
    }

    if (isset($values['drawdown_id'])) {
      Assert::integer(
        $values['payout_process_id'] ?? NULL,
        'Payout process ID is required for drawdown tasks'
      );
      $entityValues['activity_type_id:name'] = ActivityTypeNames::DRAWDOWN_TASK;
      $entityValues['source_record_id'] = $values['drawdown_id'];
    }
    elseif (isset($values['payout_process_id'])) {
      throw new \InvalidArgumentException('Payout process tasks are not supported');
    }
    elseif (isset($values['clearing_process_id'])) {
      Assert::integer(
        $values['application_process_id'] ?? NULL,
        'Application process ID is required for clearing process tasks'
      );
      $entityValues['activity_type_id:name'] = ActivityTypeNames::CLEARING_PROCESS_TASK;
      $entityValues['source_record_id'] = $values['clearing_process_id'];
    }
    elseif (isset($values['application_process_id'])) {
      $entityValues['activity_type_id:name'] = ActivityTypeNames::APPLICATION_PROCESS_TASK;
      $entityValues['source_record_id'] = $values['application_process_id'];
    }
    else {
      $entityValues['activity_type_id:name'] = ActivityTypeNames::FUNDING_CASE_TASK;
      $entityValues['source_record_id'] = $values['funding_case_id'];
    }

    if (isset($values['external_url']) && !isset($values['external_url_label'])) {
      throw new \InvalidArgumentException('external_url_label is required if external_url given');
    }
    elseif (isset($values['external_url_label']) && !isset($values['external_url'])) {
      throw new \InvalidArgumentException('external_url is required if external_url_label given');
    }

    return static::fromArray($entityValues);
  }

  /**
   * @phpstan-return taskNameT
   */
  public function getActivityTypeName(): string {
    return $this->values['activity_type_id:name'];
  }

  public function getStatusName(): string {
    return $this->values['status_id:name'];
  }

  public function setStatusName(string $statusName): self {
    if ($statusName !== $this->getStatusName()) {
      $this->values['status_id:name'] = $statusName;
      unset($this->values['status_id']);
    }

    return $this;
  }

  /**
   * @phpstan-return list<string>
   */
  public function getRequiredPermissions(): ?array {
    return $this->values['funding_case_task.required_permissions'] ?? NULL;
  }

  public function getType(): string {
    return $this->values['funding_case_task.type'];
  }

  public function getAffectedIdentifier(): string {
    return $this->values['funding_case_task.affected_identifier'];
  }

  public function getFundingCaseId(): int {
    return $this->values['funding_case_task.funding_case_id'];
  }

  public function getDueDate(): ?\DateTimeInterface {
    return self::toDateTimeOrNull($this->values['funding_case_task.due_date']);
  }

  public function setDueDate(?\DateTimeInterface $dueDate): static {
    $this->values['funding_case_task.due_date'] = self::toDateStrOrNull($dueDate);

    return $this;
  }

  public function getExternalUrl(): ?string {
    return $this->values['funding_case_task.external_url'];
  }

  public function setExternalUrl(?string $externalUrl): static {
    $this->values['funding_case_task.external_url'] = $externalUrl;

    return $this;
  }

  public function getExternalUrlLabel(): ?string {
    return $this->values['funding_case_task.external_url_label'];
  }

  public function setExternalUrlLabel(?string $externalUrlLabel): static {
    $this->values['funding_case_task.external_url_label'] = $externalUrlLabel;

    return $this;
  }

  public function getApplicationProcessId(): ?int {
    return $this->values['funding_application_process_task.application_process_id'] ?? NULL;
  }

  public function getClearingProcessId(): ?int {
    return $this->values['funding_clearing_process_task.clearing_process_id'] ?? NULL;
  }

  /**
   * @phpstan-return array<string, mixed>
   *   Values to persist (funding_case_task.required_permissions are JSON
   *   encoded).
   */
  public function toPersistArray(): array {
    return [
      'funding_case_task.required_permissions' => NULL === $this->getRequiredPermissions() ? NULL
      : json_encode($this->getRequiredPermissions(), JSON_THROW_ON_ERROR),
    ] + $this->toArray();
  }

}
