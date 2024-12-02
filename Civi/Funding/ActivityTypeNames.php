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

namespace Civi\Funding;

/**
 * @phpstan-type taskNameT ActivityTypeNames::APPLICATION_PROCESS_TASK|ActivityTypeNames::CLEARING_PROCESS_TASK|ActivityTypeNames::DRAWDOWN_TASK|ActivityTypeNames::FUNDING_CASE_TASK
 */
final class ActivityTypeNames {

  public const APPLICATION_PROCESS_TASK = 'funding_application_process_task';

  public const CLEARING_PROCESS_TASK = 'funding_clearing_process_task';

  public const DRAWDOWN_TASK = 'funding_drawdown_task';

  public const FUNDING_APPLICATION_CREATE = 'funding_application_create';

  public const FUNDING_APPLICATION_STATUS_CHANGE = 'funding_application_status_change';

  public const FUNDING_APPLICATION_COMMENT_INTERNAL = 'funding_application_comment_internal';

  public const FUNDING_APPLICATION_REVIEW_STATUS_CHANGE = 'funding_application_review_status_change';

  public const FUNDING_APPLICATION_COMMENT_EXTERNAL = 'funding_application_comment_external';

  public const FUNDING_APPLICATION_TASK_INTERNAL = 'funding_application_task_internal';

  public const FUNDING_APPLICATION_TASK_EXTERNAL = 'funding_application_task_external';

  public const FUNDING_APPLICATION_RESTORE = 'funding_application_restore';

  public const FUNDING_CASE_TASK = 'funding_case_task';

  public const FUNDING_CLEARING_CREATE = 'funding_clearing_create';

  public const FUNDING_CLEARING_STATUS_CHANGE = 'funding_clearing_status_change';

  public const FUNDING_CLEARING_REVIEW_STATUS_CHANGE = 'funding_clearing_review_status_change';

  /**
   * @phpstan-return list<taskNameT>
   */
  public static function getTasks(): array {
    return [
      self::APPLICATION_PROCESS_TASK,
      self::CLEARING_PROCESS_TASK,
      self::DRAWDOWN_TASK,
      self::FUNDING_CASE_TASK,
    ];
  }

}
