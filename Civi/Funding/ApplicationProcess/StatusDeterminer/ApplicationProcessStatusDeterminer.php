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

/**
 * @phpstan-type statusActionStatusMapT array<string|null, array<string, string>>
 */
class ApplicationProcessStatusDeterminer implements ApplicationProcessStatusDeterminerInterface {

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

  public function getStatus(string $currentStatus, string $action): string {
    $status = $this->statusActionStatusMap[$currentStatus][$action] ?? NULL;
    if (NULL === $status) {
      throw new \InvalidArgumentException(
        \sprintf(
          'Could not determine application process status for action "%s" and current status "%s"',
          $action,
          $currentStatus,
        )
      );
    }

    return $status;
  }

}