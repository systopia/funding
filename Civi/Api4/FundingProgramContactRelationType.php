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

use Civi\Api4\Generic\AbstractEntity;
use Civi\Funding\Api4\Action\FundingProgramContactRelationType\GetAction;
use Civi\Funding\Api4\Action\FundingProgramContactRelationType\GetFieldsAction;
use Civi\Funding\Api4\Traits\AdministerPermissionsTrait;

final class FundingProgramContactRelationType extends AbstractEntity {

  use AdministerPermissionsTrait;

  public static function get(): GetAction {
    return new GetAction();
  }

  /**
   * @inheritDoc
   */
  public static function getFields() {
    return new GetFieldsAction();
  }

}
