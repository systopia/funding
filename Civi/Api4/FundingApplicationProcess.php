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

namespace Civi\Api4;

use Civi\Funding\Api4\Action\FundingApplication\GetAction;
use Civi\RemoteTools\Api4\Traits\EntityNameTrait;

/**
 * FundingApplicationProcess entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @phpstan-type applicationProcessT array{
 *   id: int,
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   title: string,
 *   short_description: string,
 *   start_date: string|null,
 *   end_date: string|null,
 *   request_data: array<string, mixed>,
 *   amount_requested: float,
 *   amount_granted: float|null,
 *   granted_budget: float|null,
 *   is_review_content: bool|null,
 *   is_review_calculative: bool|null,
 * }
 */
class FundingApplicationProcess extends Generic\DAOEntity {

  use EntityNameTrait;

  /**
   * @inheritDoc
   *
   * @return \Civi\Funding\Api4\Action\FundingApplication\GetAction
   */
  public static function get($checkPermissions = TRUE) {
    return \Civi::service(GetAction::class)->setCheckPermissions($checkPermissions);
  }

}
