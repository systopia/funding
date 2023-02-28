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

namespace Civi\Funding\FundingProgram;

use Civi\Api4\FundingProgram;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @phpstan-type fundingProgramT array{
 *   id?: int,
 *   title: string,
 *   abbreviation: string,
 *   start_date: string,
 *   end_date: string,
 *   requests_start_date: string,
 *   requests_end_date: string,
 *   currency: string,
 *   budget: float|null,
 *   permissions?: array<string>,
 * }
 */
class FundingProgramManager {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function get(int $id): ?FundingProgramEntity {
    $action = FundingProgram::get(FALSE)
      ->addWhere('id', '=', $id);

    /** @var fundingProgramT|null $values */
    $values = $this->api4->executeAction($action)->first();

    return NULL === $values ? NULL : FundingProgramEntity::fromArray($values);
  }

}
