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

use Civi\Funding\Api4\Action\RecipientContactRelationType\GetAction;
use Civi\Funding\Api4\Action\RecipientContactRelationType\GetFieldsAction;
use Civi\RemoteTools\Api4\Traits\EntityNameTrait;

final class FundingRecipientContactRelationType extends Generic\AbstractEntity {

  use EntityNameTrait;

  public static function get(): GetAction {
    return \Civi::service(GetAction::class);
  }

  /**
   * @inheritDoc
   */
  public static function getFields() {
    return new GetFieldsAction();
  }

}
