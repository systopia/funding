<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\ActionStatusInfo;

/**
 * @codeCoverageIgnore
 */
final class ReworkPossibleApplicationProcessActionStatusInfo extends ApplicationProcessActionStatusInfoDecorator {

  public function isApplyAction(string $action): bool {
    return 'apply' === $action || parent::isApplyAction($action);
  }

  public function isChangeRequiredStatus(string $status): bool {
    return 'rework' === $status || parent::isChangeRequiredStatus($status);
  }

  public function isRestoreAction(string $action): bool {
    return in_array($action, ['withdraw-change', 'revert-change', 'reject-change'], TRUE)
      || parent::isRestoreAction($action);
  }

  public function isReviewStartAction(string $action): bool {
    return 'review' === $action || parent::isReviewStartAction($action);
  }

  public function isReviewStatus(string $status): bool {
    return 'rework-review' === $status || parent::isReviewStatus($status);
  }

}
