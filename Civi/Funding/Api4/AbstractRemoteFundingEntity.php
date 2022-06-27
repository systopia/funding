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

namespace Civi\Funding\Api4;

use Civi\Api4\Generic\AbstractEntity;
use Civi\Funding\Api4\Action\RemoteFundingCheckAccessAction;
use Civi\Funding\Api4\Action\RemoteFundingGetFieldsAction;

class AbstractRemoteFundingEntity extends AbstractEntity {

  /**
   * @inerhitDoc
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public static function checkAccess() {
    return new RemoteFundingCheckAccessAction(static::getEntityName(), __FUNCTION__);
  }

  /**
   * @inheritDoc
   */
  public static function getFields() {
    return new RemoteFundingGetFieldsAction(static::getEntityName(), __FUNCTION__);
  }

  /**
   * @inheritDoc
   *
   * @return array<string, array<string|string[]>>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public static function permissions(): array {
    return [
      'meta' => ['access CiviCRM', 'access Remote Funding'],
      'default' => ['administer CiviCRM'],
      'get' => ['access CiviCRM', 'access Remote Funding'],
    ];
  }

}
