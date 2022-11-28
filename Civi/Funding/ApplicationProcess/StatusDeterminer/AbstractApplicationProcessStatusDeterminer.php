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

namespace Civi\Funding\ApplicationProcess\StatusDeterminer;

use Civi\Funding\Entity\FullApplicationProcessStatus;

/**
 * @phpstan-type statusActionStatusMapT array<string|null, array<string, string>>
 */
abstract class AbstractApplicationProcessStatusDeterminer implements ApplicationProcessStatusDeterminerInterface {

  /**
   * @phpstan-var statusActionStatusMapT
   */
  private $statusActionStatusMap;

  /**
   * @phpstan-param statusActionStatusMapT $statusActionStatusMap
   */
  public function __construct(array $statusActionStatusMap) {
    $this->statusActionStatusMap = $statusActionStatusMap;
  }

  public function getInitialStatus(string $action): string {
    $status = $this->statusActionStatusMap[NULL][$action] ?? NULL;
    if (NULL === $status) {
      throw new \InvalidArgumentException(\sprintf(
        'Could not determine application process status for action "%s"',
        $action
      ));
    }

    return $status;
  }

  public function getStatus(FullApplicationProcessStatus $currentStatus, string $action): FullApplicationProcessStatus {
    $status = $this->statusActionStatusMap[$currentStatus->getStatus()][$action] ?? NULL;
    if (NULL === $status) {
      throw new \InvalidArgumentException(
        \sprintf(
          'Could not determine application process status for action "%s" and current status "%s"',
          $action,
          $currentStatus->getStatus(),
        )
      );
    }

    return new FullApplicationProcessStatus(
      $status,
      $this->getIsReviewCalculative($currentStatus, $action),
      $this->getIsReviewContent($currentStatus, $action),
    );
  }

  abstract protected function getIsReviewCalculative(
    FullApplicationProcessStatus $currentStatus,
    string $action
  ): ?bool;

  abstract protected function getIsReviewContent(FullApplicationProcessStatus $currentStatus, string $action): ?bool;

}
