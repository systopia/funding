<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Api4\OptionsLoaderInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;

final class AVK1StatusMarkupFactory {

  private ApplicationProcessActivityManager $activityManager;

  private OptionsLoaderInterface $optionsLoader;

  public function __construct(
    ApplicationProcessActivityManager $activityManager,
    OptionsLoaderInterface $optionsLoader
  ) {
    $this->activityManager = $activityManager;
    $this->optionsLoader = $optionsLoader;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function buildStatusMarkup(ApplicationProcessEntityBundle $applicationProcessBundle): string {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $statusChangeActivities = $this->getLastStatusChangeActivities($applicationProcess);
    $content = [];

    $content[] = sprintf(
      'Erstellt am: %s',
      $applicationProcess->getCreationDate()->format('d.m.Y')
    );

    $content[] = sprintf(
      'Geändert am: %s',
      $applicationProcess->getModificationDate()->format('d.m.Y')
    );

    if (isset($statusChangeActivities['applied']) && NULL !== $statusChangeActivities['applied']->getCreatedDate()) {
      $content[] = sprintf(
        'Zuletzt beantragt am: %s',
        $statusChangeActivities['applied']->getCreatedDate()->format('d.m.Y')
      );
    }

    if (TRUE === $applicationProcess->getIsReviewContent()) {
      $content[] = 'Die inhaltliche Prüfung war erfolgreich.';
    }

    if (TRUE === $applicationProcess->getIsReviewCalculative()) {
      $content[] = 'Die rechnerische Prüfung war erfolgreich.';
    }

    $content[] = sprintf(
      'Aktueller Status: %s',
      $this->optionsLoader->getOptionLabel(
        FundingApplicationProcess::getEntityName(),
        'status',
        $applicationProcess->getStatus()
      )
    );

    return '<p>' . implode('<br>', $content) . '</p>';
  }

  /**
   * @phpstan-return array<\Civi\Funding\Entity\ActivityEntity>
   *
   * @throws \CRM_Core_Exception
   */
  private function getLastStatusChangeActivities(ApplicationProcessEntity $applicationProcess): array {
    $activities = [];
    foreach ($this->activityManager->getByApplicationProcessAndType(
      $applicationProcess->getId(),
      ActivityTypeNames::FUNDING_APPLICATION_STATUS_CHANGE,
    ) as $activity) {
      // @phpstan-ignore offsetAccess.invalidOffset
      $activities[$activity->get('to_status')] ??= $activity;
    }

    return $activities;
  }

}
