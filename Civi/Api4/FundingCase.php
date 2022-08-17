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

use Civi\Funding\Api4\Action\FundingCase\GetAction;
use Civi\Funding\Api4\Action\FundingCase\GetFieldsAction;
use Civi\RemoteTools\Api4\Traits\EntityNameTrait;

/**
 * FundingCase entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @phpstan-type fundingCaseT array{
 *   id: int,
 *   funding_program_id: int,
 *   funding_case_type_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   recipient_contact_id: int,
 * }
 */
class FundingCase extends Generic\DAOEntity {

  use EntityNameTrait;

  /**
   * @inheritDoc
   *
   * @return \Civi\Funding\Api4\Action\FundingCase\GetAction
   */
  public static function get($checkPermissions = TRUE) {
    return (new GetAction(\Civi::dispatcher()))->setCheckPermissions($checkPermissions);
  }

  /**
   * @inheritDoc
   *
   * @return \Civi\Funding\Api4\Action\FundingCase\GetFieldsAction
   */
  public static function getFields($checkPermissions = TRUE) {
    return (new GetFieldsAction())->setCheckPermissions($checkPermissions);
  }

}
