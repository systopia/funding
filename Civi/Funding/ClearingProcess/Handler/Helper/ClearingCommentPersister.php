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

namespace Civi\Funding\ClearingProcess\Handler\Helper;

use Civi\Funding\ActivityTypeNames;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use CRM_Funding_ExtensionUtil as E;

final class ClearingCommentPersister {

  private ApplicationProcessActivityManager $activityManager;

  private RequestContextInterface $requestContext;

  public function __construct(
    ApplicationProcessActivityManager $activityManager,
    RequestContextInterface $requestContext
  ) {
    $this->activityManager = $activityManager;
    $this->requestContext = $requestContext;
  }

  /**
   * @param 'internal'|'external' $commentType
   *
   * @throws \CRM_Core_Exception
   */
  public function persistComment(
    ClearingProcessEntityBundle $clearingProcessBundle,
    string $commentType,
    string $commentText,
    string $action
  ): void {
    $this->activityManager->addActivity(
      $this->requestContext->getContactId(),
      $clearingProcessBundle->getApplicationProcess(),
      $this->createActivity($commentType, $commentText, $action)
    );
  }

  private function createActivity(string $commentType, string $commentText, string $action): ActivityEntity {
    return ActivityEntity::fromArray([
      'activity_type_id:name' => $this->getActivityTypeName($commentType),
      'subject' => E::ts('Funding Clearing Comment'),
      'details' => str_replace("\n", '<br>', htmlentities($commentText, ENT_SUBSTITUTE)),
      'funding_application_comment.action' => $action,
    ]);
  }

  private function getActivityTypeName(string $commentType): string {
    return 'external' === $commentType
      ? ActivityTypeNames::FUNDING_APPLICATION_COMMENT_EXTERNAL
      : ActivityTypeNames::FUNDING_APPLICATION_COMMENT_INTERNAL;
  }

}
