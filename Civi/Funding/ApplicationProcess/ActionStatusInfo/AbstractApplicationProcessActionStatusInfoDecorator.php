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
 *
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
abstract class AbstractApplicationProcessActionStatusInfoDecorator implements ApplicationProcessActionStatusInfoInterface {
// phpcs:enable

  private ApplicationProcessActionStatusInfoInterface $info;

  public function __construct(ApplicationProcessActionStatusInfoInterface $info) {
    $this->info = $info;
  }

  public function isApplyAction(string $action): bool {
    return $this->info->isApplyAction($action);
  }

  public function isDeleteAction(string $action): bool {
    return $this->info->isDeleteAction($action);
  }

  public function isRestoreAction(string $action): bool {
    return $this->info->isRestoreAction($action);
  }

  public function isReviewStartAction(string $action): bool {
    return $this->info->isReviewStartAction($action);
  }

}
