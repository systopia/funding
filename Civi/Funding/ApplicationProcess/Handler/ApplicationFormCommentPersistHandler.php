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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ActivityTypeIds;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCommentPersistCommand;
use Civi\Funding\Entity\ActivityEntity;
use CRM_Funding_ExtensionUtil as E;

final class ApplicationFormCommentPersistHandler implements ApplicationFormCommentPersistHandlerInterface {

  private ApplicationProcessActivityManager $activityManager;

  public function __construct(ApplicationProcessActivityManager $activityManager) {
    $this->activityManager = $activityManager;
  }

  public function handle(ApplicationFormCommentPersistCommand $command): void {
    $this->activityManager->addActivity(
      $command->getContactId(),
      $command->getApplicationProcess(),
      $this->createActivity($command)
    );
  }

  private function createActivity(ApplicationFormCommentPersistCommand $command): ActivityEntity {
    return ActivityEntity::fromArray([
      'activity_type_id' => ActivityTypeIds::FUNDING_APPLICATION_COMMENT,
      'subject' => E::ts('Application process comment'),
      'details' => str_replace("\n", '<br>', htmlentities($command->getComment(), ENT_SUBSTITUTE)),
      'funding_application_comment.action' => $command->getValidatedData()->getAction(),
    ]);
  }

}
