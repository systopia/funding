<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\Upgrade;

use Civi\Api4\Activity;
use Civi\Api4\FundingApplicationSnapshot;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

/**
 * Creates a snapshot creation activity for each last snapshot per application
 * process. The date is tried to figured out. It is either the creation date
 * of the last status change activity with a from status that requires a
 * snapshot, or the modification date of the application process if no such
 * activity could be found.
 */
final class Upgrader0024 implements UpgraderInterface {

  /**
   * @var array<string, list<string>>
   *   Mapping of funding case type name to list of names of statuses for which
   *   a snapshot is required.
   */
  private array $snapshotRequiredStatusNames = [];

  public function __construct(
    private readonly Api4Interface $api4,
    private readonly ApplicationProcessActivityManager $activityManager,
    private readonly ApplicationProcessManager $applicationProcessManager,
    private readonly FundingCaseTypeMetaDataProviderInterface $metaDataProvider
  ) {}

  public function execute(\Log $log): void {
    $snapshotGet = FundingApplicationSnapshot::get(FALSE)
      ->addSelect(
        'MAX(id) AS max_id',
        'application_process_id',
        'MAX(activity.id) AS max_activity_id',
        'application_process_id.funding_case_id.funding_case_type_id.name'
      )
      ->addJoin(
        'EntityActivity AS entity_activity',
        'LEFT',
        NULL,
        ['entity_activity.entity_table', '=', '"civicrm_funding_application_process"'],
        ['entity_activity.entity_id', '=', 'application_process_id']
      )
      ->addJoin(
        'Activity AS activity',
        'LEFT',
        NULL,
        ['activity.activity_type_id:name', '=', '"funding_application_snapshot_creation"'],
        ['activity.id', '=', 'entity_activity.activity_id']
      )
      ->addGroupBy('application_process_id')->setHaving([
        ['max_activity_id', 'IS NULL'],
      ]);
    $snapshots = $this->api4->executeAction($snapshotGet);

    /** @var array{
     *   max_id: int,
     *   application_process_id: int,
     *   "application_process_id.funding_case_id.funding_case_type_id.name": string,
     * } $snapshot
     */
    foreach ($snapshots as $snapshot) {
      $applicationProcess = $this->applicationProcessManager->get($snapshot['application_process_id']);
      assert(NULL !== $applicationProcess);

      $fundingCaseTypeName = $snapshot['application_process_id.funding_case_id.funding_case_type_id.name'];
      $snapshotRequiredStatusNames = $this->getSnapshotRequiredStatusNames($fundingCaseTypeName);
      $activity = NULL;
      if ([] !== $snapshotRequiredStatusNames) {
        $activityGet = Activity::get(FALSE)
          ->addSelect('id', 'created_date', 'activity_date_time')
          ->addJoin(
            'EntityActivity AS entity_activity',
            'INNER',
            NULL,
            ['entity_activity.activity_id', '=', 'id'],
            ['entity_activity.entity_table', '=', '"civicrm_funding_application_process"'],
            ['entity_activity.entity_id', '=', $snapshot['application_process_id']]
          )
          ->addWhere('activity_type_id:name', '=', ActivityTypeNames::FUNDING_APPLICATION_STATUS_CHANGE)
          ->addWhere('funding_application_status_change.from_status', 'IN', $snapshotRequiredStatusNames)
          ->addOrderBy('id', 'DESC')
          ->setLimit(1);
        /** @var array{id: int, created_date: string}|null $activity */
        $activity = $this->api4->executeAction($activityGet)->first();
      }
      if (NULL === $activity) {
        $date = $applicationProcess->getModificationDate()->format('Y-m-d H:i:s');
        $dateSource = E::ts('modification date of application process');
      }
      else {
        $date = $activity['created_date'];
        $dateSource = E::ts('creation date of activity %1', [1 => $activity['id']]);
      }

      $activity = ActivityEntity::fromArray([
        'activity_type_id:name' => ActivityTypeNames::FUNDING_APPLICATION_SNAPSHOT_CREATION,
        'activity_date_time' => $date,
        'subject' => E::ts('Funding Application Snapshot Created'),
        'details' => E::ts('Application: %1 (%2)', [
          1 => $applicationProcess->getTitle(),
          2 => $applicationProcess->getIdentifier(),
        ]) . '<br>'
        . E::ts('Created during update for existing snapshot. Date (%1) may be inaccurate.', [1 => $dateSource]),
        'funding_application_snapshot_creation.snapshot_id' => $snapshot['max_id'],
      ]);
      $this->activityManager->addActivity($applicationProcess, $activity);

      // CiviCRM ignores a given activity created_date so we have to set it directly.
      \CRM_Core_DAO::executeQuery('UPDATE civicrm_activity SET created_date = %1 WHERE id = %2', [
        1 => [$date, 'String'],
        2 => [$activity->getId(), 'Integer'],
      ]);
    }
  }

  /**
   * @return list<string>
   */
  private function getSnapshotRequiredStatusNames(string $funingCaseTypeName): array {
    if (isset($this->snapshotRequiredStatusNames[$funingCaseTypeName])) {
      return $this->snapshotRequiredStatusNames[$funingCaseTypeName];
    }

    $metaData = $this->metaDataProvider->get($funingCaseTypeName);
    $statusList = array_values(array_filter(
      $metaData->getApplicationProcessStatuses(),
      fn ($status) => $status->isSnapshotRequired()
    ));

    return $this->snapshotRequiredStatusNames[$funingCaseTypeName]
      = array_map(fn ($status) => $status->getName(), $statusList);
  }

}
