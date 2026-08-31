<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\AddApplicantCommentAction;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class AddApplicantCommentActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  public function __construct(
    private readonly ApplicationProcessActionsDeterminerInterface $actionsDeterminer,
    private readonly ApplicationProcessActivityManager $activityManager,
    private readonly ApplicationProcessManager $applicationProcessManager,
  ) {}

  /**
   * @phpstan-return array{
   *   id: int,
   *   subject: string,
   *   details: string,
   *   ...
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function addApplicantComment(AddApplicantCommentAction $action): array {
    $applicationProcessBundle = $this->applicationProcessManager->getBundle($action->getApplicationProcessId());
    Assert::notNull($applicationProcessBundle);

    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $statusList = $this->applicationProcessManager->getStatusList($applicationProcessBundle);

    if (!$this->actionsDeterminer->isActionAllowed('add-applicant-comment', $applicationProcessBundle, $statusList)) {
      throw new UnauthorizedException(
        "Permission to add an applicant comment to application process {$applicationProcess->getId()} is missing"
      );
    }

    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_APPLICATION_COMMENT_APPLICANT,
      'subject' => E::ts('Funding Application Comment'),
      'details' => str_replace("\n", '<br>', htmlentities($action->getText(), ENT_SUBSTITUTE)),
    ]);

    $this->activityManager->addActivity($applicationProcess, $activity);

    // @phpstan-ignore return.type
    return $activity->toArray();
  }

}
